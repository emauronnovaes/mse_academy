<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';
require_once __DIR__ . '/../../../src/IntegracaoApi.php';
require_once __DIR__ . '/../../../src/PortalFichaApi.php';
require_once __DIR__ . '/../../../src/Admitidos.php';

/**
 * Relatório de admitidos: quem a API de integração do RH diz que foi
 * admitido no período, e se fez a integração na Academy.
 *
 * Os dados de cada admitido são completados pela API de ficha de
 * funcionários (e-mail, departamento, obra, gerente... a ficha inteira vai
 * em "ficha"), consultada em paralelo.
 *
 * Cruza pelo CPF (users.cpf, gravado no login a partir da ficha do
 * Portal); sem CPF, pelo e-mail da ficha e, por último, pelo nome. A integração conta como feita
 * quando todas as aulas obrigatórias da trilha DESSA pessoa estão
 * concluídas (com sorteio, cada um tem a sua versão da trilha).
 *
 * GET ?data_de=YYYY-MM-DD&data_ate=YYYY-MM-DD  (padrão: últimos 30 dias)
 */

mse_cors();
mse_require_admin();

// Data de hoje no horário de Brasília (o PHP do servidor pode estar em outro fuso).
$hoje = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
$de = (string) ($_GET['data_de'] ?? '');
$ate = (string) ($_GET['data_ate'] ?? '');
$valida = static fn(string $d): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
if (!$valida($ate)) {
    $ate = $hoje;
}
if (!$valida($de)) {
    $de = date('Y-m-d', strtotime($ate . ' -30 days'));
}
if ($de > $ate) {
    [$de, $ate] = [$ate, $de];
}

$pdo = mse_db();

// ---- Lote de fichas (POST): o navegador manda até 12 admitidos já listados
// e recebe as linhas completas, com a ficha de funcionário. É chamado em
// sequência, depois que a lista já apareceu na tela.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_time_limit(120);
    $lote = mse_input()['pessoas'] ?? [];
    if (!is_array($lote) || !$lote || count($lote) > 12) {
        mse_error('Mande de 1 a 12 pessoas por vez.', 422);
    }
    $admitidos = [];
    foreach ($lote as $p) {
        $data = (string) ($p['data_admissao'] ?? '');
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
    $fichas = mse_portal_ficha_buscar_varias(array_map(
        static fn($a) => ['cpf' => $a['cpf'], 'nome' => $a['nome']],
        $admitidos
    ));
    $erro = null;
    foreach ($fichas as $f) {
        if ($f['ficha'] === null && $f['erro'] !== null) {
            $erro = $f['erro'];
            break;
        }
    }
    $r = mse_admitidos_montar($pdo, $admitidos, $fichas, $hoje);
    mse_json(['pessoas' => $r['pessoas'], 'ficha_erro' => $erro]);
}

// ---- Lista (GET): só a API de integração, sem esperar as fichas. Rápida;
// as fichas vêm depois, em lotes.
set_time_limit(90);
$aviso = null;
try {
    $admitidos = mse_integracao_admitidos($de, $ate, $aviso);
} catch (Throwable $e) {
    mse_error($e->getMessage(), 502);
}

$r = mse_admitidos_montar($pdo, $admitidos, [], $hoje);

mse_json([
    'periodo' => ['de' => $de, 'ate' => $ate],
    'resumo' => $r['resumo'],
    'pessoas' => $r['pessoas'],
    'aviso' => $aviso,
]);
