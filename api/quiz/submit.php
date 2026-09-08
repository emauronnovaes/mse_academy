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
$questionId = (int) ($input['question_id'] ?? 0);
$optionId = (int) ($input['option_id'] ?? 0);

if ($questionId <= 0 || $optionId <= 0) {
    mse_error('question_id e option_id são obrigatórios.', 422);
}

$pdo = mse_db();

// Confirma que a opção realmente pertence à pergunta enviada
// (evita alguém forjar um option_id de outra pergunta pra "acertar" fácil).
$stmt = $pdo->prepare(
    'SELECT o.id, o.is_correct, q.course_id
     FROM quiz_options o
     JOIN quiz_questions q ON q.id = o.question_id
     WHERE o.id = ? AND o.question_id = ?'
);
$stmt->execute([$optionId, $questionId]);
$option = $stmt->fetch();

if (!$option) {
    mse_error('Pergunta ou opção inválida.', 422);
}

$courseId = (int) $option['course_id'];

if (!mse_course_is_unlocked($pdo, (int) $user['id'], $courseId)) {
    mse_error('Este módulo ainda está bloqueado.', 403);
}

// Exige ter assistido o vídeo (>=95%) antes de aceitar qualquer resposta —
// mesma regra usada no player, agora reforçada no servidor.
$stmt = $pdo->prepare(
    'SELECT watched_pct FROM user_course_progress WHERE user_id = ? AND course_id = ?'
);
$stmt->execute([$user['id'], $courseId]);
$progress = $stmt->fetch();

if (!$progress || (float) $progress['watched_pct'] < 95) {
    mse_error('Assista o vídeo até o fim antes de responder.', 403);
}

$isCorrect = (bool) $option['is_correct'];

$stmt = $pdo->prepare(
    'INSERT INTO quiz_attempts (user_id, question_id, option_id, is_correct) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$user['id'], $questionId, $optionId, $isCorrect ? 1 : 0]);

if ($isCorrect) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "UPDATE user_course_progress
             SET status = 'concluido', completed_at = COALESCE(completed_at, NOW())
             WHERE user_id = ? AND course_id = ?"
        );
        $stmt->execute([$user['id'], $courseId]);

        $points = 10; // pontuação fixa por módulo — ajuste como quiser
        $stmt = $pdo->prepare(
            'INSERT INTO user_stats (user_id, total_points, modules_completed)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE
                total_points = total_points + VALUES(total_points),
                modules_completed = modules_completed + 1'
        );
        $stmt->execute([$user['id'], $points]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        mse_error('Erro ao registrar conclusão do módulo.', 500);
    }
}

mse_json(['correct' => $isCorrect]);
