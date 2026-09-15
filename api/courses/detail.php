<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';
require_once __DIR__ . '/../../src/AwsS3.php';

mse_cors();
$user = mse_require_auth();

$courseId = (int) ($_GET['id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Parâmetro "id" é obrigatório.', 422);
}

$pdo = mse_db();

$stmt = $pdo->prepare(
    'SELECT c.id, c.title, c.description, c.youtube_id, c.video_source, c.video_key,
            c.duration_minutes, c.type,
            a.slug AS area_slug, a.name AS area_name
     FROM courses c
     LEFT JOIN areas a ON a.id = c.area_id
     WHERE c.id = ? AND c.is_published = 1
     LIMIT 1'
);
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    mse_error('Curso não encontrado.', 404);
}

if (!mse_course_is_unlocked($pdo, (int) $user['id'], $courseId)) {
    mse_error('Este módulo ainda está bloqueado. Conclua os anteriores primeiro.', 403);
}

// Vídeo do S3: gera uma URL assinada de curta duração (30 min é de sobra
// pra assistir um módulo — se a pessoa demorar mais que isso, o
// front-end pode simplesmente pedir os detalhes do curso de novo pra
// ganhar um link novo). Nunca devolvemos o "video_key" cru pro
// navegador — só a URL já pronta e assinada.
$videoUrl = null;
if ($course['video_source'] === 's3' && $course['video_key']) {
    try {
        $videoUrl = mse_s3_presigned_url($course['video_key'], 1800);
    } catch (Throwable $e) {
        // AWS mal configurado no servidor — não derruba a página toda,
        // só devolve sem vídeo e loga pra alguém do time de infra ver.
        error_log('[mse_s3_presigned_url] ' . $e->getMessage());
    }
}
unset($course['video_key']); // nunca sai do servidor

// Perguntas + opções, SEM o campo is_correct (isso só é validado no servidor no submit)
$stmt = $pdo->prepare(
    'SELECT id, question_text, order_index FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC'
);
$stmt->execute([$courseId]);
$questions = $stmt->fetchAll();

foreach ($questions as &$question) {
    $stmt2 = $pdo->prepare(
        'SELECT id, option_text, order_index FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC'
    );
    $stmt2->execute([$question['id']]);
    $question['id'] = (int) $question['id'];
    $question['options'] = array_map(function ($opt) {
        $opt['id'] = (int) $opt['id'];
        return $opt;
    }, $stmt2->fetchAll());
}

$course['id'] = (int) $course['id'];
$course['duration_minutes'] = (int) $course['duration_minutes'];
$course['video_url'] = $videoUrl; // preenchido quando video_source='s3'; null quando é youtube_id mesmo
$course['questions'] = $questions;

mse_json(['course' => $course]);
