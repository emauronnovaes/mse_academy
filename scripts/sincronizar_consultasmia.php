<?php
declare(strict_types=1);

/**
 * Atualiza sozinho o banco de consulta (integracao_admitidos, consultasmia e
 * consultasmia_2) — é o "Atualizar banco" da tela de Relatórios, automático.
 * Roda via cron, NÃO é chamado pelo navegador (por isso não tem limite de
 * tempo do site: a atualização inteira leva alguns minutos).
 *
 * Como agendar (a cada hora, por exemplo):
 *   crontab -e
 *   0 * * * * php /caminho/completo/scripts/sincronizar_consultasmia.php >> /var/log/mse-academy-sync.log 2>&1
 *
 * Também dá pra rodar à mão quando quiser:
 *   php scripts/sincronizar_consultasmia.php
 *   php scripts/sincronizar_consultasmia.php 2026-07-01 2026-10-13   (período de admissão)
 *
 * Sem datas, olha as admissões dos últimos 90 dias. Usa as mesmas
 * configurações do .env do site (nada de senha aqui no script).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

set_time_limit(0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/IntegracaoBanco.php';
require_once __DIR__ . '/../src/Viniconsultas.php';

$agoraBr = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$valida = static fn($d): bool => is_string($d) && (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
$ate = isset($argv[2]) && $valida($argv[2]) ? $argv[2] : $agoraBr->format('Y-m-d');
$de = isset($argv[1]) && $valida($argv[1]) ? $argv[1] : date('Y-m-d', strtotime($ate . ' -90 days'));
if ($de > $ate) {
    [$de, $ate] = [$ate, $de];
}

$inicio = microtime(true);
echo '[' . $agoraBr->format('Y-m-d H:i:s') . "] Atualizando o banco de consulta ({$de} a {$ate})...\n";

try {
    $r = mse_sincronizar_integracao(mse_db(), $de, $ate);
} catch (Throwable $e) {
    fwrite(STDERR, 'ERRO: ' . $e->getMessage() . "\n");
    exit(1);
}

// Todos os funcionários ativos da ficha (sem limite de tempo aqui).
$f = ['processados' => 0, 'total' => 0, 'terminou' => false, 'erro' => null];
$primeira = true;
do {
    $r2 = mse_vini_importar_funcionarios_lote(mse_db(), 600.0, $primeira);
    $primeira = false;
    $f['processados'] += $r2['processados'];
    $f['total'] = $r2['total'] ?: $f['total'];
    $f['erro'] = $r2['erro'];
} while (!$r2['terminou'] && $r2['erro'] === null && $r2['total'] > 0);
echo $f['erro'] ? "Funcionários da ficha: ERRO {$f['erro']}\n" : "Funcionários da ficha: {$f['processados']} de {$f['total']} atualizados.\n";

printf(
    "OK em %.1fs: %d pessoas pendentes, %d concluíram, %d dispensadas, %d novas, %d linhas em consultasmia.%s\n",
    microtime(true) - $inicio,
    (int) ($r['pendentes'] ?? 0), (int) ($r['concluiram'] ?? 0), (int) ($r['dispensados'] ?? 0),
    (int) ($r['novos'] ?? 0), (int) ($r['consultasmia'] ?? 0),
    !empty($r['aviso']) ? "\nAviso: " . $r['aviso'] : ''
);
