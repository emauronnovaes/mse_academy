<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Email.php';
require_once __DIR__ . '/../../../src/EmailAprovacao.php';

/**
 * Manda um e-mail de teste, devolvendo o erro exato do servidor de e-mail
 * (ou da Central de disparos) se falhar — é o jeito de conferir a
 * configuração do .env sem esperar alguém enviar um vídeo. Nunca devolve
 * usuário nem senha.
 *
 * O teste é o MESMO e-mail do aviso de verdade, montado com o vídeo enviado
 * mais recente (ou com dados de exemplo, se ainda não houver nenhum), e com
 * "[TESTE]" no assunto. Vai pro próprio admin (SMTP) ou pros destinatários
 * do cadastro na Central de disparos.
 *
 * POST
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$pdo = mse_db();
$dados = null;
if (mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    $id = (int) $pdo->query(
        'SELECT id FROM courses WHERE aprovacao_status IS NOT NULL ORDER BY enviado_em DESC, id DESC LIMIT 1'
    )->fetchColumn();
    if ($id > 0) {
        $dados = mse_email_aprovacao_dados($pdo, $id);
    }
}
$dados = $dados ?? mse_email_aprovacao_exemplo($admin);

$r = mse_enviar_email(
    [(string) $admin['email']],
    '[TESTE] Vídeo para aprovação: ' . $dados['titulo'] . ' · MSE Academy',
    mse_email_aprovacao_html($dados),
    mse_email_aprovacao_texto($dados)
);
mse_json([
    'ok' => $r['ok'],
    // Pela Central, quem recebe é quem está no cadastro do envio, não o admin.
    'para' => mse_disparo_configurado() ? 'os destinatários cadastrados na Central de disparos' : $admin['email'],
    'erro' => $r['erro'],
]);
