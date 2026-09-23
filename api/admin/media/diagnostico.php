<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/AwsS3.php';
require_once __DIR__ . '/../../../src/Progress.php';       // mse_normalize_text()
require_once __DIR__ . '/../../../src/PortalFichaApi.php'; // ficha funcional (cargo/setor)

mse_cors();
$user = mse_require_admin();

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

// ------------------------------------------------------------
// API de ficha funcional (cargo e setor do RH)
// ------------------------------------------------------------
// Mesma ideia do bloco da AWS: sem ver o que o servidor enxerga, a
// investigação vira tentativa e erro. O token nunca é devolvido — só
// o tamanho e se ainda é o texto de exemplo do .env.example.
$pdo = mse_db();
$fichaToken = trim(mse_env('PORTAL_FICHA_API_TOKEN'));
$ficha = [
    'base' => trim(mse_env('PORTAL_FICHA_API_BASE')),
    'token_preenchido' => $fichaToken !== '',
    'token_tamanho' => strlen($fichaToken),
    'token_e_exemplo' => $fichaToken !== '' && mse_str_contains(mse_normalize_text($fichaToken), 'troque'),
    'curl_disponivel' => function_exists('curl_init'),
];

// Só testa de verdade quando o token parece real — chamar com o texto
// de exemplo só geraria um 401 previsível.
if ($fichaToken !== '' && !$ficha['token_e_exemplo'] && function_exists('curl_init')) {
    $usuario = $pdo->query('SELECT name, cpf FROM users WHERE id = ' . (int) $user['id'])->fetch();
    $termo = !empty($usuario['cpf']) ? $usuario['cpf'] : (string) ($usuario['name'] ?? '');
    $ficha['testado_com'] = !empty($usuario['cpf']) ? 'CPF' : 'nome';
    try {
        $r = mse_portal_ficha_buscar($termo);
        $ficha['encontrou'] = $r !== null;
        // Devolve só se os campos vieram, nunca o conteúdo — é dado
        // pessoal de RH.
        $ficha['trouxe_cargo'] = $r !== null && !empty($r['funcao']);
        $ficha['trouxe_setor'] = $r !== null && !empty($r['obras_departamento']);
    } catch (Throwable $e) {
        $ficha['erro'] = substr($e->getMessage(), 0, 200);
    }
} else {
    $ficha['testado_com'] = null;
    $ficha['motivo_nao_testado'] = $fichaToken === ''
        ? 'token vazio no .env'
        : ($ficha['token_e_exemplo'] ? 'token ainda é o texto de exemplo' : 'extensão curl ausente');
}

// O que já está gravado do usuário logado — se a API funciona mas isto
// continua vazio, o problema é outro (ex: o nome não achou ficha).
$meu = $pdo->query('SELECT cargo, area_id FROM users WHERE id = ' . (int) $user['id'])->fetch();
$ficha['meu_cargo_no_banco'] = $meu['cargo'] ?: null;
$ficha['minha_area_no_banco'] = $meu['area_id']
    ? $pdo->query('SELECT name FROM areas WHERE id = ' . (int) $meu['area_id'])->fetchColumn()
    : null;

mse_json([
    'ficha_funcional' => $ficha,
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
