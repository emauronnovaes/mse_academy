<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

/**
 * "Meus vídeos" > Quem assistiu: as pessoas que assistiram um vídeo que a
 * própria pessoa enviou, quanto assistiram e o que responderam em cada
 * pergunta (tentativas e se acertou).
 *
 * Só para o dono do vídeo (courses.enviado_por).
 *
 * GET ?course_id=N
 */

mse_cors();
$usuario = mse_require_auth();
$pdo = mse_db();

$courseId = (int) ($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}
if (!mse_tem_coluna($pdo, 'courses', 'enviado_por')) {
    mse_error('Vídeo não encontrado.', 404);
}
$st = $pdo->prepare('SELECT id, title FROM courses WHERE id = ? AND enviado_por = ?');
$st->execute([$courseId, (int) $usuario['id']]);
$video = $st->fetch();
if (!$video) {
    mse_error('Vídeo não encontrado entre os que você enviou.', 404);
}

$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$st = $pdo->prepare(
    'SELECT q.id, q.question_text, ' . ($temMomento ? 'q.momento_seg' : 'NULL AS momento_seg') . ',
            (SELECT o.option_text FROM quiz_options o WHERE o.question_id = q.id AND o.is_correct = 1 ORDER BY o.order_index LIMIT 1) AS correta
     FROM quiz_questions q WHERE q.course_id = ?
     ORDER BY ' . ($temMomento ? 'q.momento_seg IS NULL, q.momento_seg, ' : '') . 'q.order_index, q.id'
);
$st->execute([$courseId]);
$perguntas = $st->fetchAll();

// Quem assistiu alguma parte (ou respondeu alguma pergunta) do vídeo.
$oculto = mse_tem_coluna($pdo, 'users', 'oculto_em_relatorios') ? ' AND COALESCE(u.oculto_em_relatorios, 0) = 0' : '';
$st = $pdo->prepare(
    "SELECT u.id, u.name, p.status, p.watched_pct, p.completed_at
     FROM users u
     LEFT JOIN user_course_progress p ON p.user_id = u.id AND p.course_id = ?
     WHERE u.id <> ?{$oculto} AND (
           (p.id IS NOT NULL AND (p.status = 'concluido' OR p.watched_pct > 0))
        OR EXISTS (SELECT 1 FROM quiz_attempts t JOIN quiz_questions q ON q.id = t.question_id WHERE q.course_id = ? AND t.user_id = u.id))
     ORDER BY p.completed_at IS NULL, p.completed_at DESC, u.name"
);
$st->execute([$courseId, (int) $usuario['id'], $courseId]);
$pessoas = $st->fetchAll();

$tentativas = [];
if ($perguntas && $pessoas) {
    $st = $pdo->prepare(
        'SELECT t.user_id, t.question_id, t.is_correct, t.attempted_at, o.option_text
         FROM quiz_attempts t
         JOIN quiz_questions q ON q.id = t.question_id
         LEFT JOIN quiz_options o ON o.id = t.option_id
         WHERE q.course_id = ?
         ORDER BY t.id'
    );
    $st->execute([$courseId]);
    foreach ($st->fetchAll() as $t) {
        $tentativas[(int) $t['user_id']][(int) $t['question_id']][] = [
            'em' => $t['attempted_at'],
            'resposta' => $t['option_text'],
            'correta' => (bool) $t['is_correct'],
        ];
    }
}

mse_json([
    'video' => ['id' => (int) $video['id'], 'titulo' => $video['title']],
    'total_perguntas' => count($perguntas),
    'pessoas' => array_map(static function ($p) use ($perguntas, $tentativas) {
        $doUsuario = $tentativas[(int) $p['id']] ?? [];
        return [
            'nome' => $p['name'],
            'status' => $p['status'] ?: 'nao_iniciado',
            'percentual' => $p['watched_pct'] !== null ? (int) $p['watched_pct'] : 0,
            'concluido_em' => $p['completed_at'],
            'perguntas' => array_map(static function ($q) use ($doUsuario) {
                $ts = $doUsuario[(int) $q['id']] ?? [];
                return [
                    'pergunta' => $q['question_text'],
                    'momento_seg' => $q['momento_seg'] !== null ? (int) $q['momento_seg'] : null,
                    'resposta_certa' => $q['correta'],
                    'tentativas' => count($ts),
                    'acertou_de_primeira' => $ts ? $ts[0]['correta'] : null,
                    'situacao' => !$ts ? 'nao_respondeu' : (end($ts)['correta'] ? 'acertou' : 'errou'),
                    'respostas' => $ts,
                ];
            }, $perguntas),
        ];
    }, $pessoas),
]);
