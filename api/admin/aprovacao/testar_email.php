<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Email.php';

/**
 * Manda um e-mail de teste pro próprio admin, devolvendo o erro exato do
 * servidor de e-mail se falhar — é o jeito de conferir o SMTP do .env sem
 * esperar alguém enviar um vídeo. Nunca devolve usuário nem senha.
 *
 * POST
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$r = mse_enviar_email(
    [(string) $admin['email']],
    '[MSE Academy] Teste de e-mail',
    '<p style="font-family:Arial,sans-serif;font-size:14px">Se você recebeu esta mensagem, os avisos de vídeo para aprovação estão funcionando.</p>',
    'Se você recebeu esta mensagem, os avisos de vídeo para aprovação estão funcionando.'
);
mse_json(['ok' => $r['ok'], 'para' => $admin['email'], 'erro' => $r['erro']]);
