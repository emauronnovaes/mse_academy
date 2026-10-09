<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';

mse_cors();
// Admin, ou o dono do vídeo (quem o enviou) — só o dele. Conferido abaixo.
$usuario = mse_require_auth();
$ehAdmin = ($usuario['role'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();

$courseId = (int) ($input['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$pdo = mse_db();
mse_exigir_admin_ou_dono($pdo, $usuario, $courseId);

$stmt = $pdo->prepare('SELECT id, title, video_source, video_key FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    mse_error('Aula não encontrada.', 404);
}

// As FKs são ON DELETE CASCADE (migração 001), então apagar o curso
// leva junto quiz e histórico. Contamos ANTES pra que o admin veja o
// tamanho do estrago antes de confirmar — um treinamento já assistido
// costuma ser o que comprova capacitação numa auditoria.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM user_course_progress WHERE course_id = ?');
$stmt->execute([$courseId]);
$progressCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM quiz_attempts qa
     JOIN quiz_questions qq ON qq.id = qa.question_id
     WHERE qq.course_id = ?'
);
$stmt->execute([$courseId]);
$attemptCount = (int) $stmt->fetchColumn();

// Quem não é admin só exclui o PRÓPRIO vídeo e só se mais ninguém o assistiu:
// o histórico de quem assistiu é evidência de treinamento (auditoria) e não
// pode sumir por uma exclusão do dono. Nesse caso, um admin exclui.
if (!$ehAdmin) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_course_progress WHERE course_id = ? AND user_id <> ?');
    $stmt->execute([$courseId, (int) $usuario['id']]);
    $outrosAssistiram = (int) $stmt->fetchColumn();
    if ($outrosAssistiram > 0) {
        mse_error("Este vídeo já foi assistido por {$outrosAssistiram} " . ($outrosAssistiram === 1 ? 'pessoa' : 'pessoas')
            . ' e o histórico precisa ser mantido. Peça a um administrador para excluir.', 409);
    }
}

// Confirmação escrita: só apaga se vier o título exato da aula, no
// mesmo espírito do GitHub ao excluir repositório. Sem isso, devolve
// 409 com o impacto pro front montar o aviso.
$confirmacao = trim((string) ($input['confirmacao'] ?? ''));

if ($confirmacao === '') {
    mse_json([
        'error' => 'Confirmação necessária.',
        'confirmacao_esperada' => $course['title'],
        'impacto' => [
            'titulo' => $course['title'],
            'colaboradores_com_progresso' => $progressCount,
            'respostas_de_quiz' => $attemptCount,
            'video_no_s3' => $course['video_source'] === 's3' ? $course['video_key'] : null,
        ],
        'aviso' => 'Isso apaga o histórico de quem assistiu. Para só tirar do ar sem perder nada, use archive.php.',
    ], 409);
}

if ($confirmacao !== $course['title']) {
    mse_error('A confirmação não bate com o título da aula. Nada foi apagado.', 422);
}

// Dono excluindo o próprio vídeo: o arquivo da pasta de envios também sai da
// AWS (⚠ irreversível), igual quando um admin recusa. (Admin excluindo mantém
// o arquivo, como sempre foi.)
$arquivoApagado = false;
if (!$ehAdmin) {
    $arquivoApagado = mse_excluir_arquivo_recusado($pdo, $courseId)['excluido'];
}

$stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
$stmt->execute([$courseId]);

// O arquivo no S3 é mantido de propósito: o bucket não tem versionamento,
// então apagar aqui seria perda definitiva do vídeo.
mse_json([
    'removido' => [
        'id' => $courseId,
        'titulo' => $course['title'],
        'registros_de_progresso_apagados' => $progressCount,
        'respostas_de_quiz_apagadas' => $attemptCount,
    ],
    'video_mantido_no_s3' => $course['video_source'] === 's3' ? $course['video_key'] : null,
    'message' => 'Aula excluída definitivamente.',
]);
