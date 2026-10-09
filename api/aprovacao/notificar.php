<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Aprovacao.php';
require_once __DIR__ . '/../../src/Email.php';
require_once __DIR__ . '/../../src/EmailAprovacao.php';

/**
 * Avisa os admins, por e-mail, que um vídeo está esperando aprovação.
 *
 * Chamado pela tela depois que o vídeo, as perguntas e os departamentos
 * foram salvos — assim o e-mail já descreve o envio completo. Só quem
 * enviou pode chamar, e o aviso sai uma vez só por vídeo.
 *
 * POST {course_id}
 */

mse_cors();
$usuario = mse_require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$courseId = (int) (mse_input()['course_id'] ?? 0);
$pdo = mse_db();
if ($courseId <= 0 || !mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    mse_error('Vídeo não encontrado.', 404);
}

$stmt = $pdo->prepare(
    'SELECT c.id, c.title, c.description, c.type, c.video_source, c.duration_minutes, c.aprovacao_status,
            c.enviado_por, c.enviado_em, c.notificado_em, a.name AS area_name
     FROM courses c LEFT JOIN areas a ON a.id = c.area_id
     WHERE c.id = ?'
);
$stmt->execute([$courseId]);
$curso = $stmt->fetch();
if (!$curso || (int) $curso['enviado_por'] !== (int) $usuario['id']) {
    mse_error('Vídeo não encontrado.', 404);
}
if ($curso['aprovacao_status'] !== 'pendente' || $curso['notificado_em'] !== null) {
    mse_json(['email_enviado' => false, 'ja_avisado' => true]);
}

$admins = $pdo->query("SELECT email FROM users WHERE role = 'admin' AND active = 1 AND email <> ''")->fetchAll();
$emails = array_column($admins, 'email');

$dados = mse_email_aprovacao_dados($pdo, $courseId);
$html = mse_email_aprovacao_html($dados);
$texto = mse_email_aprovacao_texto($dados);

$resultado = mse_enviar_email($emails, 'Vídeo para aprovação: ' . $curso['title'] . ' · MSE Academy', $html, $texto);
if ($resultado['ok']) {
    $pdo->prepare('UPDATE courses SET notificado_em = NOW() WHERE id = ?')->execute([$courseId]);
}

// O motivo exato da falha fica no log do servidor e no "Testar e-mail" dos
// admins; aqui a pessoa só precisa saber que o vídeo está na fila.
mse_json(['email_enviado' => $resultado['ok'], 'admins' => count($emails)]);
