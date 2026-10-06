<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

/**
 * Check-in e check-out do log de presença.
 *
 * POST {course_id, evento: "checkin" | "checkout"}
 *
 * O player manda checkin ao abrir o vídeo e checkout ao sair (fechar o
 * vídeo, trocar de aula, fechar a aba ou o vídeo terminar). Data, hora e
 * % assistido são do servidor — o navegador só diz o que aconteceu.
 */

mse_cors();
$user = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$evento = (string) ($input['evento'] ?? '');
if ($courseId <= 0 || !in_array($evento, ['checkin', 'checkout'], true)) {
    mse_error('Informe course_id e evento (checkin ou checkout).', 422);
}

$pdo = mse_db();
$stmt = $pdo->prepare('SELECT 1 FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
if ($stmt->fetchColumn() === false) {
    mse_error('Treinamento não encontrado.', 404);
}

mse_json(['ok' => true, 'gravado' => mse_registrar_evento($pdo, (int) $user['id'], $courseId, $evento)]);
