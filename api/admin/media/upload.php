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

// Upload de arquivo usa multipart/form-data, não JSON — o arquivo vem
// em $_FILES, os outros campos em $_POST (não em mse_input()).
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo maior que o limite do servidor (upload_max_filesize no php.ini).',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo maior que o limite permitido no formulário.',
        UPLOAD_ERR_PARTIAL => 'Upload interrompido no meio — tente de novo.',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado (campo "video" vazio).',
    ];
    $code = $_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE;
    mse_error($errorMessages[$code] ?? 'Falha no upload do arquivo.', 422);
}

$destinationKey = trim((string) ($_POST['destination_key'] ?? ''));
if ($destinationKey === '') {
    mse_error('Informe destination_key — o caminho de destino dentro do bucket (ex: "onboarding/modulo-1-visao-geral.mp4").', 422);
}

// Só permite subir vídeo (não deixa alguém usar isso pra hospedar
// qualquer tipo de arquivo no bucket da empresa).
$allowedExtensions = ['mp4', 'webm', 'mov'];
$ext = strtolower(pathinfo($destinationKey, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExtensions, true)) {
    mse_error('Extensão não permitida. Use .mp4, .webm ou .mov.', 422);
}

// Trava simples contra "../" no destino (não deixa escrever fora da
// estrutura esperada do bucket usando caminho relativo malicioso).
if (mse_str_contains($destinationKey, '..')) {
    mse_error('destination_key inválido.', 422);
}

try {
    $savedKey = mse_s3_upload_file($_FILES['video']['tmp_name'], $destinationKey);
} catch (RuntimeException $e) {
    mse_error($e->getMessage(), 502);
}

mse_json([
    'video_key' => $savedKey,
    'message' => 'Vídeo enviado com sucesso. Agora associe esse video_key a um curso (courses.video_key, video_source="s3").',
]);
