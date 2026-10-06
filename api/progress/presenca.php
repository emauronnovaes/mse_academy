<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

// Cada clique em "Estou aqui" no aviso de presença do vídeo. Para a
// auditoria é a evidência de que a pessoa estava diante da tela durante o
// treinamento, e não só com a aba aberta.

mse_cors();
$user = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('course_id é obrigatório.', 422);
}

$pdo = mse_db();

// Migração 020 ainda não rodou: não há onde gravar. Não é erro pra quem
// está assistindo — o vídeo segue normalmente.
if (!mse_tem_coluna($pdo, 'user_course_progress', 'confirmacoes_presenca')) {
    mse_json(['ok' => true, 'gravado' => false]);
}

// Só conta pra quem já abriu o treinamento (o player grava a abertura
// antes de qualquer aviso aparecer).
$stmt = $pdo->prepare(
    'UPDATE user_course_progress
     SET confirmacoes_presenca = confirmacoes_presenca + 1, ultima_confirmacao_em = NOW()
     WHERE user_id = ? AND course_id = ?'
);
$stmt->execute([$user['id'], $courseId]);
$gravado = $stmt->rowCount() > 0;
if ($gravado) {
    mse_registrar_evento($pdo, (int) $user['id'], $courseId, 'presenca'); // entra no log com data e hora
}

mse_json(['ok' => true, 'gravado' => $gravado]);
