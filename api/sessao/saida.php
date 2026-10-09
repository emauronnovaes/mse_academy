<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Viniconsultas.php';

/**
 * A pessoa saiu da Academy (fechou a aba ou foi para outro site). A Academy
 * não tem botão de sair (a sessão vem do Portal e dura 30 dias), então este
 * é o "logout" na prática. Vai para o log (viniconsultas_eventos).
 *
 * POST
 */

mse_cors();
$usuario = mse_require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}
mse_vini_registrar_evento((int) $usuario['id'], 'saida', ['origem' => 'academy']);
mse_json(['ok' => true]);
