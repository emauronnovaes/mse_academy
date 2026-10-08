<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Aprovacao.php';
require_once __DIR__ . '/../../src/Email.php';

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
    'SELECT c.id, c.title, c.description, c.type, c.video_source, c.aprovacao_status, c.enviado_por,
            c.notificado_em, a.name AS area_name,
            (SELECT COUNT(*) FROM quiz_questions q WHERE q.course_id = c.id) AS perguntas
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

$stmt = $pdo->prepare('SELECT name, email, cargo FROM users WHERE id = ?');
$stmt->execute([(int) $usuario['id']]);
$autor = $stmt->fetch() ?: ['name' => '', 'email' => '', 'cargo' => ''];

$link = mse_url_academy() . '/?aprovar=' . $courseId;
$e = static function ($v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$onde = $curso['type'] === 'onboarding' ? 'Integração (trilha obrigatória)' : 'Curso · ' . ($curso['area_name'] ?: 'sem área');
$origem = ['youtube' => 'YouTube', 's3' => 'Arquivo enviado', 'playlist' => 'Playlist do YouTube'][$curso['video_source']] ?? $curso['video_source'];
$quem = trim($autor['name'] . ($autor['cargo'] ? ' · ' . $autor['cargo'] : ''));
$linhas = [
    'Título' => $curso['title'],
    'Enviado por' => $quem . ($autor['email'] ? " ({$autor['email']})" : ''),
    'Onde aparece' => $onde,
    'Origem' => $origem,
    'Perguntas' => (int) $curso['perguntas'] ?: 'nenhuma',
];
if (trim((string) $curso['description']) !== '') {
    $linhas['Descrição'] = $curso['description'];
}

$tabela = '';
$texto = "Um vídeo foi enviado para a MSE Academy e está esperando aprovação.\n\n";
foreach ($linhas as $rotulo => $valor) {
    $tabela .= '<tr><td style="padding:6px 12px 6px 0;color:#5C6470;font-size:13px;vertical-align:top;white-space:nowrap">'
        . $e($rotulo) . '</td><td style="padding:6px 0;font-size:14px;color:#1C1B1A">' . nl2br($e($valor)) . '</td></tr>';
    $texto .= "{$rotulo}: {$valor}\n";
}
$texto .= "\nAbrir tela de aprovação: {$link}\n\nEle só aparece para os colaboradores depois de aprovado.";

$html = '<!doctype html><html><body style="margin:0;background:#F3F2EF;font-family:Arial,Helvetica,sans-serif">'
    . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F2EF;padding:24px 12px"><tr><td align="center">'
    . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2dfd9">'
    . '<tr><td style="background:#C4212C;color:#fff;padding:18px 24px;font-size:16px;font-weight:bold">MSE Academy · Vídeo para aprovação</td></tr>'
    . '<tr><td style="padding:22px 24px">'
    . '<p style="margin:0 0 14px;font-size:14px;color:#1C1B1A">Um vídeo foi enviado e está esperando aprovação. Ele só aparece para os colaboradores depois que um administrador aprovar.</p>'
    . '<table role="presentation" cellpadding="0" cellspacing="0">' . $tabela . '</table>'
    . '<p style="margin:22px 0 6px"><a href="' . $e($link) . '" style="display:inline-block;background:#C4212C;color:#fff;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:8px;font-size:15px">Abrir tela de aprovação</a></p>'
    . '<p style="margin:10px 0 0;font-size:12px;color:#5C6470">Se pedir login, entre na MSE Academy pelo Portal: a tela de aprovação abre sozinha logo depois. Também dá para usar o botão <b>Aprovações</b> na barra de admin.</p>'
    . '</td></tr></table></td></tr></table></body></html>';

$resultado = mse_enviar_email($emails, '[MSE Academy] Vídeo para aprovação: ' . $curso['title'], $html, $texto);
if ($resultado['ok']) {
    $pdo->prepare('UPDATE courses SET notificado_em = NOW() WHERE id = ?')->execute([$courseId]);
}

// O motivo exato da falha fica no log do servidor e no "Testar e-mail" dos
// admins; aqui a pessoa só precisa saber que o vídeo está na fila.
mse_json(['email_enviado' => $resultado['ok'], 'admins' => count($emails)]);
