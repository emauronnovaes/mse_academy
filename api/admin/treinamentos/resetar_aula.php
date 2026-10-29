<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';

/**
 * Reinicia UMA aula de UMA pessoa: ela volta a assistir do começo.
 *
 * Faz o mesmo que a Academy faz quando a pessoa reprova: % assistido volta a
 * zero, a conclusão é desfeita e as respostas anteriores deixam de contar
 * (as tentativas ficam gravadas, só não valem mais). O log de check-in/out
 * também fica.
 *
 * POST {user_id, course_id}
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$in = mse_input();
$userId = (int) ($in['user_id'] ?? 0);
$courseId = (int) ($in['course_id'] ?? 0);
if ($userId <= 0 || $courseId <= 0) {
    mse_error('Informe user_id e course_id.', 422);
}

$pdo = mse_db();
$stmt = $pdo->prepare(
    'SELECT p.status, p.watched_pct, p.completed_at, u.name, c.title
     FROM user_course_progress p
     JOIN users u ON u.id = p.user_id
     JOIN courses c ON c.id = p.course_id
     WHERE p.user_id = ? AND p.course_id = ?'
);
$stmt->execute([$userId, $courseId]);
$antes = $stmt->fetch();
if (!$antes) {
    mse_error('Essa pessoa ainda não tem progresso nessa aula: não há o que resetar.', 404);
}

$sets = ["watched_pct = 0", "status = 'nao_iniciado'", 'completed_at = NULL'];
$params = [];
if (mse_garantir_rodada_quiz($pdo)) {
    $sets[] = 'quiz_rodada_desde = ?';
    $params[] = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM quiz_attempts')->fetchColumn();
}
if (mse_garantir_refazer_quiz($pdo)) {
    $sets[] = 'quiz_refazer_desde = 0';
}
if (mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em')) {
    $sets[] = 'checkin_em = NULL';
}
$params[] = $userId;
$params[] = $courseId;
$pdo->prepare('UPDATE user_course_progress SET ' . implode(', ', $sets) . ' WHERE user_id = ? AND course_id = ?')->execute($params);

error_log(sprintf(
    '[resetar_aula] admin #%d (%s) resetou "%s" de %s (user %d, course %d); antes: status=%s pct=%s concluido=%s',
    (int) ($admin['id'] ?? 0), (string) ($admin['name'] ?? ''), $antes['title'], $antes['name'], $userId, $courseId,
    $antes['status'], $antes['watched_pct'], $antes['completed_at'] ?? '-'
));

mse_json(['ok' => true, 'aula' => $antes['title'], 'pessoa' => $antes['name']]);
