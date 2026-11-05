<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';

/**
 * Respostas de uma pessoa às perguntas dos vídeos: cada pergunta, quantas
 * tentativas, o que respondeu em cada uma e se acertou.
 *
 * GET ?user_id=N [&course_id=N]   (sem course_id: todos os vídeos que ela respondeu)
 */

mse_cors();
mse_require_admin();

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    mse_error('Informe user_id.', 422);
}
$courseId = (int) ($_GET['course_id'] ?? 0);

$pdo = mse_db();
$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');

// Perguntas dos vídeos em que a pessoa respondeu alguma coisa (ou do vídeo pedido).
$sql = "SELECT c.id AS course_id, c.title, q.id AS question_id, q.question_text, q.order_index,
               " . ($temMomento ? 'q.momento_seg' : 'NULL AS momento_seg') . ",
               (SELECT o.option_text FROM quiz_options o WHERE o.question_id = q.id AND o.is_correct = 1 ORDER BY o.order_index LIMIT 1) AS correta
        FROM quiz_questions q
        JOIN courses c ON c.id = q.course_id
        WHERE " . ($courseId > 0
            ? 'c.id = ?'
            : 'c.id IN (SELECT q2.course_id FROM quiz_attempts t2 JOIN quiz_questions q2 ON q2.id = t2.question_id WHERE t2.user_id = ?)') . "
        ORDER BY c.title, c.id, " . ($temMomento ? 'q.momento_seg IS NULL, q.momento_seg, ' : '') . 'q.order_index, q.id';
$st = $pdo->prepare($sql);
$st->execute([$courseId > 0 ? $courseId : $userId]);
$perguntas = $st->fetchAll();

$tentativas = [];
if ($perguntas) {
    $ids = array_map(static fn($p) => (int) $p['question_id'], $perguntas);
    $st = $pdo->prepare(
        'SELECT t.question_id, t.is_correct, t.attempted_at, o.option_text
         FROM quiz_attempts t LEFT JOIN quiz_options o ON o.id = t.option_id
         WHERE t.user_id = ? AND t.question_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')
         ORDER BY t.id'
    );
    $st->execute(array_merge([$userId], $ids));
    foreach ($st->fetchAll() as $t) {
        $tentativas[(int) $t['question_id']][] = [
            'em' => $t['attempted_at'],
            'resposta' => $t['option_text'],
            'correta' => (bool) $t['is_correct'],
        ];
    }
}

$cursos = [];
foreach ($perguntas as $p) {
    $cid = (int) $p['course_id'];
    $ts = $tentativas[(int) $p['question_id']] ?? [];
    $cursos[$cid] ??= ['course_id' => $cid, 'titulo' => $p['title'], 'perguntas' => []];
    $cursos[$cid]['perguntas'][] = [
        'pergunta' => $p['question_text'],
        'momento_seg' => $p['momento_seg'] !== null ? (int) $p['momento_seg'] : null,
        'resposta_certa' => $p['correta'],
        'tentativas' => count($ts),
        'acertou_de_primeira' => $ts ? $ts[0]['correta'] : null,
        // Situação pela última resposta: é a que vale depois de refazer.
        'situacao' => !$ts ? 'nao_respondeu' : (end($ts)['correta'] ? 'acertou' : 'errou'),
        'respostas' => $ts,
    ];
}

mse_json(['cursos' => array_values($cursos)]);
