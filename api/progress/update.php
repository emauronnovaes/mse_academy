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

// Aula COM pergunta nunca conclui aqui — quem conclui é o
// quiz/submit.php, depois da resposta certa. Sem essa distinção a
// pergunta viraria decorativa: dava pra pular e o curso já aparecia
// como feito.
//
// Mas aula SEM pergunta cadastrada não tem como ser concluída pelo
// quiz, então ficaria "em andamento" pra sempre e a trilha nunca
// avançaria. Nesse caso, assistir até o fim é o que conclui — quem
// decide é o admin, ao cadastrar (ou não) uma pergunta na aula.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM quiz_questions WHERE course_id = ?');
$stmt->execute([$courseId]);
$temPergunta = (int) $stmt->fetchColumn() > 0;

$concluiuAssistindo = !$temPergunta && $watchedPct >= 95;

$status = $concluiuAssistindo ? 'concluido' : ($watchedPct > 0 ? 'em_andamento' : 'nao_iniciado');
$completedAt = $concluiuAssistindo ? date('Y-m-d H:i:s') : null;

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

// Pontos da aula sem pergunta. O mesmo cuidado do quiz/submit.php: só
// soma na PRIMEIRA vez, senão reassistir renderia pontos de novo a cada
// vez, sem limite.
if ($concluiuAssistindo) {
    $stmt = $pdo->prepare(
        'SELECT completed_at FROM user_course_progress WHERE user_id = ? AND course_id = ?'
    );
    $stmt->execute([$user['id'], $courseId]);
    $linha = $stmt->fetch();

    // Se completed_at é igual ao que acabamos de gravar, esta é a
    // primeira conclusão (o COALESCE acima preserva o valor anterior).
    if ($linha && $linha['completed_at'] === $completedAt) {
        $stmt = $pdo->prepare(
            'INSERT INTO user_stats (user_id, total_points, modules_completed)
             VALUES (?, 10, 1)
             ON DUPLICATE KEY UPDATE
                total_points = total_points + 10,
                modules_completed = modules_completed + 1'
        );
        $stmt->execute([$user['id']]);
    }
}

mse_json(['ok' => true, 'status' => $status, 'watched_pct' => $watchedPct]);
