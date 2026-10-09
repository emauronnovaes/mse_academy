<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Viniconsultas.php';

/**
 * Registra no log (viniconsultas_eventos) que a pessoa clicou no baú da
 * trilha de integração, junto com a PROVA conferida agora no banco: se todos
 * os cursos obrigatórios estão concluídos e o status de cada um. O navegador
 * não informa nada disso — quem confere é o servidor.
 *
 * POST
 */

mse_cors();
$usuario = mse_require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$prova = mse_vini_prova_integracao(mse_db(), (int) $usuario['id']);
mse_vini_registrar_evento((int) $usuario['id'], 'bau_aberto', $prova);

mse_json(['ok' => true, 'concluida' => $prova['concluida']]);
