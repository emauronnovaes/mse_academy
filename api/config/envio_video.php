<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Configuracoes.php';

/**
 * Liga/desliga o "Adicionar vídeo" para quem não é admin.
 *
 * GET   qualquer pessoa logada: { liberado: bool } (para mostrar ou esconder o botão)
 * POST  só admin: { liberado: bool } grava o ajuste
 *
 * Desabilitado, o servidor recusa o envio (create.php e upload) de quem não é
 * admin. Vídeos que já foram enviados continuam como estão: o que está em
 * aprovação segue na fila e o que está publicado segue publicado.
 */

mse_cors();
$pdo = mse_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    mse_require_auth();
    mse_json(['liberado' => mse_envio_video_liberado($pdo)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$admin = mse_require_admin();
$input = mse_input();
if (!array_key_exists('liberado', $input)) {
    mse_error('Informe liberado (true ou false).', 422);
}
$liberado = (bool) $input['liberado'];
if (!mse_config_gravar($pdo, 'envio_video_colaboradores', $liberado ? '1' : '0', (string) ($admin['email'] ?? $admin['name'] ?? ''))) {
    mse_error('Não consegui gravar o ajuste. Falta rodar a migração 043 no banco (migrations/043_configuracoes.sql).', 409);
}
mse_json(['liberado' => $liberado]);
