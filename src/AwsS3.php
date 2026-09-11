<?php
declare(strict_types=1);

/**
 * Integração com S3 usando o SDK OFICIAL da AWS (aws/aws-sdk-php).
 *
 * Sem Composer disponível no ambiente onde isso foi construído, o SDK
 * foi baixado como um único arquivo `lib/aws.phar` (autocontido, com
 * todas as dependências já empacotadas dentro) — é a mesma forma
 * oficialmente suportada de instalar o SDK sem Composer, publicada
 * pela própria AWS em cada release: https://github.com/aws/aws-sdk-php/releases
 *
 * Se o seu servidor TEM Composer, pode trocar isso por
 * `composer require aws/aws-sdk-php` e remover o require do phar — o
 * resto do código (as funções abaixo) continua funcionando igual,
 * porque usam as mesmas classes do SDK de qualquer jeito.
 */

require_once __DIR__ . '/../config/database.php'; // de onde vem mse_env()
require_once __DIR__ . '/../lib/aws.phar';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/** Cria (uma vez só, reaproveitando) o cliente S3 configurado com as credenciais do .env. */
function mse_s3_client(): S3Client
{
    static $client = null;
    if ($client !== null) {
        return $client;
    }

    $accessKey = trim(mse_env('AWS_ACCESS_KEY_ID'));
    $secretKey = trim(mse_env('AWS_SECRET_ACCESS_KEY'));
    $region = trim(mse_env('AWS_REGION', 'us-east-1'));

    if ($accessKey === '' || $secretKey === '') {
        throw new RuntimeException(
            'AWS não configurado — preencha AWS_ACCESS_KEY_ID e AWS_SECRET_ACCESS_KEY no .env'
        );
    }

    $client = new S3Client([
        'version' => 'latest',
        'region' => $region,
        'credentials' => [
            'key' => $accessKey,
            'secret' => $secretKey,
        ],
    ]);

    return $client;
}

/** @return string a URL assinada, pronta pra usar no <video src="..."> */
function mse_s3_presigned_url(string $objectKey, int $expiresSeconds = 1800): string
{
    $bucket = trim(mse_env('AWS_S3_BUCKET'));
    if ($bucket === '') {
        throw new RuntimeException('AWS_S3_BUCKET não configurado no .env');
    }

    $client = mse_s3_client();
    $command = $client->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key' => ltrim($objectKey, '/'),
    ]);
    $request = $client->createPresignedRequest($command, "+{$expiresSeconds} seconds");

    return (string) $request->getUri();
}

/**
 * Envia um arquivo pro S3 (usado pelo endpoint de upload de vídeo do
 * admin). Devolve a "key" (caminho dentro do bucket) que foi salva —
 * é isso que se grava em courses.video_key depois.
 *
 * @param string $localTmpPath Caminho do arquivo temporário (ex: $_FILES[...]['tmp_name'])
 * @param string $destinationKey Caminho de destino dentro do bucket, ex: "onboarding/modulo-1.mp4"
 */
function mse_s3_upload_file(string $localTmpPath, string $destinationKey): string
{
    $bucket = trim(mse_env('AWS_S3_BUCKET'));
    if ($bucket === '') {
        throw new RuntimeException('AWS_S3_BUCKET não configurado no .env');
    }
    if (!is_file($localTmpPath) || !is_readable($localTmpPath)) {
        throw new RuntimeException('Arquivo temporário não encontrado ou sem permissão de leitura.');
    }

    $destinationKey = ltrim($destinationKey, '/');
    $client = mse_s3_client();

    try {
        $client->putObject([
            'Bucket' => $bucket,
            'Key' => $destinationKey,
            'SourceFile' => $localTmpPath,
            // Bucket é PRIVADO de propósito — ninguém acessa o arquivo direto,
            // só através de uma URL assinada gerada por mse_s3_presigned_url().
        ]);
    } catch (AwsException $e) {
        // getAwsErrorMessage() só vem preenchido quando a AWS respondeu um
        // XML de erro estruturado — em falha de rede/DNS/proxy no meio do
        // caminho isso fica vazio, então caímos pra getMessage() (do PHP),
        // que sempre tem alguma informação útil pra diagnosticar.
        throw new RuntimeException('Falha ao enviar pro S3: ' . ($e->getAwsErrorMessage() ?: $e->getMessage()));
    }

    return $destinationKey;
}

/**
 * Lista os arquivos dentro de uma "pasta" (prefixo) do bucket — usado
 * pelo painel de admin pra mostrar o que já está no S3 antes de decidir
 * subir um vídeo novo ou reaproveitar um já existente.
 *
 * @return array<int, array{key:string, size:int, last_modified:string}>
 */
function mse_s3_list_objects(string $prefix = '', int $maxKeys = 200): array
{
    $bucket = trim(mse_env('AWS_S3_BUCKET'));
    if ($bucket === '') {
        throw new RuntimeException('AWS_S3_BUCKET não configurado no .env');
    }

    $client = mse_s3_client();

    try {
        $result = $client->listObjectsV2([
            'Bucket' => $bucket,
            'Prefix' => ltrim($prefix, '/'),
            'MaxKeys' => $maxKeys,
        ]);
    } catch (AwsException $e) {
        throw new RuntimeException('Falha ao listar o bucket: ' . ($e->getAwsErrorMessage() ?: $e->getMessage()));
    }

    $items = [];
    foreach ($result['Contents'] ?? [] as $obj) {
        // Ignora "pastas vazias" que o S3 às vezes lista como um objeto
        // de 0 bytes terminando em "/" (não é um arquivo de verdade).
        if (mse_str_ends_with($obj['Key'], '/') && (int) $obj['Size'] === 0) {
            continue;
        }
        $items[] = [
            'key' => $obj['Key'],
            'size' => (int) $obj['Size'],
            'last_modified' => $obj['LastModified']->format('Y-m-d H:i:s'),
        ];
    }

    return $items;
}
