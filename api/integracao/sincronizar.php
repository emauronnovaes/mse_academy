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
$fase = (string) ($input['fase'] ?? '');

// ---- Atualização em partes (botão "Atualizar banco"): cada chamada é curta,
// pra nenhuma passar do tempo limite do servidor.
if ($fase !== '') {
    $pdo = mse_db();
    try {
        if ($fase === 'inicio') {
            [, $inicio] = mse_sincronizar_agora($pdo);
            mse_json(['inicio' => $inicio]);
        }
        if ($fase === 'lote') {
            $lote = $input['pessoas'] ?? [];
            if (!is_array($lote) || !$lote || count($lote) > 12) {
                mse_error('Mande de 1 a 12 pessoas por vez.', 422);
            }
            $admitidos = [];
            foreach ($lote as $p) {
                $data = is_array($p) ? (string) ($p['data_admissao'] ?? '') : '';
                if (!is_array($p) || trim((string) ($p['nome'] ?? '')) === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                    mse_error('Pessoa inválida no lote.', 422);
                }
                $admitidos[] = [
                    'nome' => mb_substr(trim((string) $p['nome']), 0, 200),
                    'cpf' => preg_replace('/\D/', '', (string) ($p['cpf'] ?? '')) ?: null,
                    'data_admissao' => $data,
                    'funcao' => isset($p['funcao']) ? mb_substr((string) $p['funcao'], 0, 200) : null,
                    'obra' => isset($p['obra']) ? mb_substr((string) $p['obra'], 0, 200) : null,
                    'vinculo' => isset($p['vinculo']) ? mb_substr((string) $p['vinculo'], 0, 60) : null,
                    'empresa' => isset($p['empresa']) ? mb_substr((string) $p['empresa'], 0, 200) : null,
                ];
            }
            mse_json(mse_sincronizar_lote($pdo, $admitidos));
        }
        if ($fase === 'antigos') {
            $inicio = (string) ($input['inicio'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $inicio)) {
                mse_error('Informe o início da atualização.', 422);
            }
            mse_json(mse_sincronizar_antigos($pdo, $inicio, 8));
        }
    } catch (Throwable $e) {
        mse_error($e->getMessage(), 502);
    }
    mse_error('fase inválida.', 422);
}

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
