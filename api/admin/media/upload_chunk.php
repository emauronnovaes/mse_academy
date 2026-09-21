<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/AwsS3.php';

mse_cors();
mse_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

/**
 * Upload de vídeo em pedaços, usando o multipart do S3.
 *
 * O upload.php original manda o arquivo inteiro numa requisição só. Isso
 * funciona pra arquivo pequeno, mas num vídeo de aula (700 MB, ~18 min de
 * envio) trava: o servidor precisa segurar tudo de uma vez, e o envio
 * inteiro se perde se qualquer coisa falhar no meio.
 *
 * Aqui cada pedaço é uma requisição pequena e independente, que vai
 * direto pro S3 como uma "parte". No fim a AWS junta tudo. Isso permite
 * barra de progresso de verdade e retentar só o pedaço que falhou, em
 * vez de recomeçar os 700 MB.
 */

$acao = (string) ($_GET['acao'] ?? '');

$validarChave = static function (string $key): string {
    $key = ltrim(trim($key), '/');
    if ($key === '') {
        mse_error('Informe destination_key.', 422);
    }
    if (mse_str_contains($key, '..')) {
        mse_error('destination_key inválido.', 422);
    }
    $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4', 'webm', 'mov'], true)) {
        mse_error('Extensão não permitida. Use .mp4, .webm ou .mov.', 422);
    }
    return $key;
};

$bucket = trim(mse_env('AWS_S3_BUCKET'));
if ($bucket === '') {
    mse_error('AWS_S3_BUCKET não configurado no .env', 500);
}

$s3 = mse_s3_client();

try {
    if ($acao === 'iniciar') {
        $input = mse_input();
        $key = $validarChave((string) ($input['destination_key'] ?? ''));

        $r = $s3->createMultipartUpload(['Bucket' => $bucket, 'Key' => $key]);

        mse_json([
            'upload_id' => $r['UploadId'],
            'destination_key' => $key,
            // O S3 exige que toda parte, menos a última, tenha no mínimo
            // 5 MB — quem manda o tamanho é o servidor pra o front não
            // ter que saber dessa regra.
            'tamanho_parte' => 5 * 1024 * 1024,
        ]);
    }

    if ($acao === 'parte') {
        $key = $validarChave((string) ($_GET['key'] ?? ''));
        $uploadId = (string) ($_GET['upload_id'] ?? '');
        $numero = (int) ($_GET['parte'] ?? 0);

        if ($uploadId === '' || $numero < 1) {
            mse_error('Informe upload_id e parte (a partir de 1).', 422);
        }

        // Os bytes vêm crus no corpo, sem multipart/form-data: evita o
        // custo de parsear formulário e deixa a requisição ser só o dado.
        $corpo = file_get_contents('php://input');
        if ($corpo === false || $corpo === '') {
            mse_error('Pedaço vazio.', 422);
        }

        $r = $s3->uploadPart([
            'Bucket' => $bucket,
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $numero,
            'Body' => $corpo,
        ]);

        // O ETag identifica a parte; o front devolve todos eles no final
        // pra AWS remontar o arquivo na ordem certa.
        mse_json(['parte' => $numero, 'etag' => $r['ETag'], 'bytes' => strlen($corpo)]);
    }

    if ($acao === 'finalizar') {
        $input = mse_input();
        $key = $validarChave((string) ($input['destination_key'] ?? ''));
        $uploadId = (string) ($input['upload_id'] ?? '');
        $partes = $input['partes'] ?? null;

        if ($uploadId === '' || !is_array($partes) || !$partes) {
            mse_error('Informe upload_id e a lista de partes.', 422);
        }

        $lista = [];
        foreach ($partes as $p) {
            $lista[] = ['PartNumber' => (int) $p['parte'], 'ETag' => (string) $p['etag']];
        }
        usort($lista, static fn($a, $b) => $a['PartNumber'] <=> $b['PartNumber']);

        $s3->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $key,
            'UploadId' => $uploadId,
            'MultipartUpload' => ['Parts' => $lista],
        ]);

        mse_json([
            'video_key' => $key,
            'partes' => count($lista),
            'message' => 'Vídeo enviado com sucesso.',
        ]);
    }

    if ($acao === 'cancelar') {
        $input = mse_input();
        $key = $validarChave((string) ($input['destination_key'] ?? ''));
        $uploadId = (string) ($input['upload_id'] ?? '');

        if ($uploadId === '') {
            mse_error('Informe upload_id.', 422);
        }

        // Sem isso as partes já enviadas ficariam ocupando espaço (e
        // custando) no bucket, invisíveis na listagem normal.
        $s3->abortMultipartUpload(['Bucket' => $bucket, 'Key' => $key, 'UploadId' => $uploadId]);

        mse_json(['message' => 'Envio cancelado e partes descartadas.']);
    }

    mse_error('acao inválida — use iniciar, parte, finalizar ou cancelar.', 422);

} catch (Throwable $e) {
    mse_error('Falha no envio pro S3: ' . $e->getMessage(), 502);
}
