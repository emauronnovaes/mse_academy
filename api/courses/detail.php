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

// Mesmo motivo do list.php: a migração pode ainda não ter sido rodada
// no servidor, e selecionar a coluna nova derrubaria a aula inteira.
$temDuracaoSegundos = false;
foreach ($pdo->query('SHOW COLUMNS FROM courses') as $col) {
    if ($col['Field'] === 'duration_seconds') { $temDuracaoSegundos = true; break; }
}

$stmt = $pdo->prepare(
    'SELECT c.id, c.title, c.description, c.youtube_id, c.video_source, c.video_key,
            c.duration_minutes, ' . ($temDuracaoSegundos ? 'c.duration_seconds' : 'NULL AS duration_seconds') . ', c.type,
            ' . (mse_tem_coluna($pdo, 'courses', 'aviso_presenca') ? 'c.aviso_presenca' : '1 AS aviso_presenca') . ',
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

// Vídeo do S3: gera uma URL assinada (4 horas). Com 30 min o link vencia
// no meio da aula (pausas, perguntas, "Estou aqui") e o vídeo ficava preto;
// se ainda assim vencer, o player pede os detalhes de novo e continua do
// mesmo ponto. Nunca devolvemos o "video_key" cru pro navegador — só a
// URL já pronta e assinada.
$videoUrl = null;
if ($course['video_source'] === 's3' && $course['video_key']) {
    try {
        $videoUrl = mse_s3_presigned_url($course['video_key'], 4 * 3600);
    } catch (Throwable $e) {
        // AWS mal configurado no servidor — não derruba a página toda,
        // só devolve sem vídeo e loga pra alguém do time de infra ver.
        error_log('[mse_s3_presigned_url] ' . $e->getMessage());
    }
}
unset($course['video_key']); // nunca sai do servidor

// Perguntas + opções, SEM o campo is_correct (isso só é validado no servidor no submit)
$stmt = $pdo->prepare(
    'SELECT id, question_text, order_index, '
        // momento_seg: em que segundo do vídeo a atividade aparece (NULL = no fim)
        . (mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg') ? 'momento_seg' : 'NULL AS momento_seg')
        . ' FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC'
);
$stmt->execute([$courseId]);
$questions = $stmt->fetchAll();

// O que esta pessoa já respondeu (e o que precisa refazer) nesta rodada.
$situacao = mse_quiz_situacao($pdo, (int) $user['id'], $courseId);

foreach ($questions as &$question) {
    $stmt2 = $pdo->prepare(
        'SELECT id, option_text, order_index FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC'
    );
    $stmt2->execute([$question['id']]);
    $question['id'] = (int) $question['id'];
    $question['momento_seg'] = $question['momento_seg'] !== null ? (int) $question['momento_seg'] : null;
    $question['respondida'] = $situacao['perguntas'][$question['id']]['respondida'] ?? false;
    $question['options'] = array_map(function ($opt) {
        $opt['id'] = (int) $opt['id'];
        return $opt;
    }, $stmt2->fetchAll());
}

$course['id'] = (int) $course['id'];
$course['duration_minutes'] = (int) $course['duration_minutes'];
$course['aviso_presenca'] = (int) $course['aviso_presenca'] === 1; // "Você ainda está aí?" ligado neste vídeo
$course['video_url'] = $videoUrl; // preenchido quando video_source='s3'; null quando é youtube_id mesmo
$course['questions'] = $questions;

// Onde o vídeo continua: refazendo as erradas, os trechos antes de cada uma;
// senão, até onde a pessoa já assistiu (atualizar a página não volta ao início).
$stmt = $pdo->prepare('SELECT status, watched_pct FROM user_course_progress WHERE user_id = ? AND course_id = ?');
$stmt->execute([(int) $user['id'], $courseId]);
$progresso = $stmt->fetch();
$emAndamento = $progresso && $progresso['status'] !== 'concluido';
$course['refazer_trechos'] = $emAndamento ? mse_quiz_trechos_refazer($situacao) : [];
$course['retomar_pct'] = $emAndamento ? (float) $progresso['watched_pct'] : 0.0;
$course['retomar_seg'] = $course['refazer_trechos'] ? $course['refazer_trechos'][0]['de'] : null;

mse_json(['course' => $course]);
