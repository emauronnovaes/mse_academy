<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

mse_cors();
$user = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$watchedPct = (float) ($input['watched_pct'] ?? 0);

if ($courseId <= 0) {
    mse_error('course_id é obrigatório.', 422);
}
$watchedPct = max(0.0, min(100.0, $watchedPct));

$pdo = mse_db();

// Confere no servidor se este módulo já pode ser assistido — não dá pra
// "destravar" um módulo fora de ordem só chamando a API direto.
if (!mse_course_is_unlocked($pdo, (int) $user['id'], $courseId)) {
    mse_error('Este módulo ainda está bloqueado.', 403);
}

$status = $watchedPct >= 95 ? 'concluido' : ($watchedPct > 0 ? 'em_andamento' : 'nao_iniciado');
$completedAt = $status === 'concluido' ? date('Y-m-d H:i:s') : null;

// GREATEST() garante que o percentual nunca regride (ex: se a pessoa voltar
// e assistir só um trecho de novo, não perde o progresso já feito).
// O status também só "sobe" — uma vez concluído, nunca volta a em_andamento.
$stmt = $pdo->prepare(
    "INSERT INTO user_course_progress (user_id, course_id, status, watched_pct, completed_at)
     VALUES (:uid, :cid, :status, :pct, :completed_at)
     ON DUPLICATE KEY UPDATE
        watched_pct = GREATEST(watched_pct, VALUES(watched_pct)),
        status = IF(status = 'concluido', 'concluido', VALUES(status)),
        completed_at = COALESCE(completed_at, VALUES(completed_at))"
);
$stmt->execute([
    'uid' => $user['id'],
    'cid' => $courseId,
    'status' => $status,
    'pct' => $watchedPct,
    'completed_at' => $completedAt,
]);

mse_json(['ok' => true, 'status' => $status, 'watched_pct' => $watchedPct]);
