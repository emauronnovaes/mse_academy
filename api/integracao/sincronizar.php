<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/IntegracaoBanco.php';

/**
 * Atualiza a tabela integracao_admitidos (a que o outro sistema consulta).
 *
 * Quem pode chamar:
 *   - um admin logado (botão "Atualizar banco" em Relatórios > Pendentes);
 *   - uma chamada agendada (cron) com o cabeçalho  X-Sync-Token: <valor de
 *     INTEGRACAO_SYNC_TOKEN no .env>. O valor fica só no .env do servidor.
 *
 * POST [{data_de, data_ate}]  — sem datas, os últimos 90 dias.
 */

mse_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$esperado = trim(mse_env('INTEGRACAO_SYNC_TOKEN'));
$enviado = (string) ($_SERVER['HTTP_X_SYNC_TOKEN'] ?? '');
$porToken = $esperado !== '' && $enviado !== '' && hash_equals($esperado, $enviado);
if (!$porToken) {
    mse_require_admin();
}

set_time_limit(300);
$input = mse_input();
$hoje = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
$valida = static fn($d): bool => is_string($d) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
$ate = $valida($input['data_ate'] ?? null) ? $input['data_ate'] : $hoje;
$de = $valida($input['data_de'] ?? null) ? $input['data_de'] : date('Y-m-d', strtotime($ate . ' -90 days'));
if ($de > $ate) {
    [$de, $ate] = [$ate, $de];
}

try {
    $r = mse_sincronizar_integracao(mse_db(), $de, $ate);
} catch (Throwable $e) {
    mse_error($e->getMessage(), 502);
}
mse_json($r + ['periodo' => ['de' => $de, 'ate' => $ate]]);
