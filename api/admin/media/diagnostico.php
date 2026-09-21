<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/AwsS3.php';

mse_cors();
mse_require_admin();

/**
 * Diz o que o servidor está enxergando da configuração da AWS, sem
 * nunca devolver o valor das credenciais.
 *
 * Existe porque "não configurado no .env" tem várias causas que dão a
 * mesma mensagem: variável ausente, nome errado (AWS_DEFAULT_REGION em
 * vez de AWS_REGION é o engano mais comum), .env no lugar errado, ou
 * valor preenchido mas inválido. Sem isso o diagnóstico vira tentativa
 * e erro no servidor de produção.
 *
 * mse_aws_region()/mse_aws_bucket() (em src/AwsS3.php) aceitam os nomes
 * errados como fallback — então "preenchida: false" aqui não quer dizer
 * mais que o envio vai falhar, só que o nome CERTO está vazio. O que
 * decide se funciona é o campo "efetivo".
 */

$esperadas = ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_REGION', 'AWS_S3_BUCKET'];

// Nomes parecidos que as pessoas usam por engano — se algum estiver
// preenchido, quase certamente é esse o problema.
$enganosComuns = [
    'AWS_DEFAULT_REGION', 'AWS_BUCKET_NAME', 'AWS_BUCKET', 'AWS_S3_REGION',
    'AWS_KEY', 'AWS_SECRET', 'AWS_ACCESS_KEY', 'AWS_SECRET_KEY',
];

$relatorio = [];
foreach ($esperadas as $nome) {
    $v = trim(mse_env($nome));
    $segredo = in_array($nome, ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY'], true);
    $relatorio[$nome] = [
        'preenchida' => $v !== '',
        // Em credencial mostramos só o tamanho: serve pra ver se veio
        // truncada ou com espaço sobrando, sem revelar o valor.
        'valor' => $segredo ? null : $v,
        'tamanho' => strlen($v),
    ];
}

$encontradosPorEngano = [];
foreach ($enganosComuns as $nome) {
    if (trim(mse_env($nome)) !== '') {
        $encontradosPorEngano[] = $nome;
    }
}

$envPath = realpath(__DIR__ . '/../../../.env');

// O que o sistema realmente vai usar, já considerando o fallback pros
// nomes errados — é isso, não a linha "AWS_REGION"/"AWS_S3_BUCKET"
// isolada acima, que decide se o upload funciona.
$efetivo = [
    'regiao' => mse_aws_region(),
    'bucket' => mse_aws_bucket(),
];

$conexao = ['testada' => false];
if ($relatorio['AWS_ACCESS_KEY_ID']['preenchida']
    && $relatorio['AWS_SECRET_ACCESS_KEY']['preenchida']
    && $efetivo['bucket'] !== '') {
    try {
        $qtd = count(mse_s3_list_objects('', 1));
        $conexao = ['testada' => true, 'ok' => true, 'detalhe' => 'Bucket acessível.'];
    } catch (Throwable $e) {
        $conexao = ['testada' => true, 'ok' => false, 'detalhe' => substr($e->getMessage(), 0, 300)];
    }
}

mse_json([
    'arquivo_env' => [
        'caminho_esperado' => $envPath ?: 'NÃO ENCONTRADO',
        'existe' => $envPath !== false,
        'legivel' => $envPath !== false && is_readable($envPath),
    ],
    'variaveis' => $relatorio,
    'nomes_errados_encontrados' => $encontradosPorEngano,
    'efetivo' => $efetivo,
    'conexao_s3' => $conexao,
    'php_version' => PHP_VERSION,
]);
