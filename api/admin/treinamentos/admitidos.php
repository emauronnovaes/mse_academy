<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';
require_once __DIR__ . '/../../../src/IntegracaoApi.php';
require_once __DIR__ . '/../../../src/PortalFichaApi.php';

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

try {
    $admitidos = mse_integracao_admitidos($de, $ate);
} catch (Throwable $e) {
    mse_error($e->getMessage(), 502);
}

$pdo = mse_db();

// Ficha de funcionários de cada admitido, tudo de uma vez (em paralelo).
$fichas = mse_portal_ficha_buscar_varias(array_map(
    static fn($a) => ['cpf' => $a['cpf'], 'nome' => $a['nome']],
    $admitidos
));
$fichaErro = null;
foreach ($fichas as $f) {
    if ($f['ficha'] === null && $f['erro'] !== null) {
        $fichaErro = $f['erro'];
        break;
    }
}

// Quem já usa a Academy, por CPF, e-mail e nome.
$porCpf = [];
$porEmail = [];
$porNome = [];
foreach ($pdo->query('SELECT id, name, email, cpf, last_access_date FROM users WHERE active = 1') as $u) {
    $cpf = preg_replace('/\D/', '', (string) $u['cpf']);
    if ($cpf !== '') {
        $porCpf[$cpf] = $u;
    }
    if ($u['email']) {
        $porEmail[strtolower(trim((string) $u['email']))] = $u;
    }
    $porNome[mse_normalize_text((string) $u['name'])][] = $u;
}

$progresso = $pdo->prepare(
    "SELECT p.course_id, p.status, p.completed_at
     FROM user_course_progress p JOIN courses c ON c.id = p.course_id
     WHERE p.user_id = ? AND c.type = 'onboarding'"
);

$resumo = ['concluiu' => 0, 'em_andamento' => 0, 'nao_iniciou' => 0, 'sem_acesso' => 0];
$pessoas = [];
foreach ($admitidos as $i => $a) {
    // Completa com a ficha: o que a API de integração não trouxe.
    $ficha = $fichas[$i]['ficha'] ?? null;
    $bruta = $fichas[$i]['bruta'] ?? null;
    $campo = static fn(string $k) => isset($bruta[$k]) && is_scalar($bruta[$k]) && trim((string) $bruta[$k]) !== '' ? trim((string) $bruta[$k]) : null;
    if ($ficha !== null) {
        $a['cpf'] = $a['cpf'] ?: ($ficha['cpf'] ?: null);
        $a['funcao'] = $ficha['funcao'] ?: $a['funcao'];
        $a['obra'] = $campo('nome_obra') ?: $a['obra'];
        $a['empresa'] = $a['empresa'] ?: $campo('empresa_contratante');
    }
    $a['email'] = $ficha['email'] ?? null;
    $a['departamento'] = $ficha['obras_departamento'] ?? null;
    $a['telefone'] = $campo('telefone');
    $a['gerente_nome'] = $campo('gerente_nome');
    $a['gerente_email'] = $campo('gerente_email');
    $a['ficha'] = $bruta; // todos os dados da ficha, pra "Ver dados"

    $usuario = null;
    $cruzouPor = null;
    if ($a['cpf'] && isset($porCpf[$a['cpf']])) {
        $usuario = $porCpf[$a['cpf']];
        $cruzouPor = 'cpf';
    } elseif ($a['email'] && isset($porEmail[strtolower($a['email'])])) {
        $usuario = $porEmail[strtolower($a['email'])];
        $cruzouPor = 'email';
    } else {
        $mesmos = $porNome[mse_normalize_text($a['nome'])] ?? [];
        if (count($mesmos) === 1) { // homônimo: melhor não chutar
            $usuario = $mesmos[0];
            $cruzouPor = 'nome';
        }
    }

    $linha = $a + [
        'dias_desde_admissao' => (int) floor((strtotime($hoje) - strtotime($a['data_admissao'])) / 86400),
        'user_id' => null, 'cruzou_por' => $cruzouPor, 'ultimo_acesso' => null,
        'obrigatorias' => 0, 'concluidas' => 0, 'concluiu_em' => null,
    ];

    if ($usuario === null) {
        $linha['situacao'] = 'sem_acesso';
    } else {
        $userId = (int) $usuario['id'];
        $linha['user_id'] = $userId;
        $linha['email'] = $linha['email'] ?: $usuario['email'];
        $linha['ultimo_acesso'] = $usuario['last_access_date'];

        $obrigatorias = array_column(array_filter(mse_aulas_da_trilha($pdo, $userId), static fn($c) => $c['obrigatorio']), 'id');
        $progresso->execute([$userId]);
        $feitas = [];
        $mexeu = false;
        foreach ($progresso->fetchAll() as $p) {
            if ($p['status'] !== 'nao_iniciado') {
                $mexeu = true;
            }
            if ($p['status'] === 'concluido') {
                $feitas[(int) $p['course_id']] = $p['completed_at'];
            }
        }
        $concluidas = array_intersect_key($feitas, array_flip($obrigatorias));
        $linha['obrigatorias'] = count($obrigatorias);
        $linha['concluidas'] = count($concluidas);

        if ($obrigatorias && count($concluidas) === count($obrigatorias)) {
            $linha['situacao'] = 'concluiu';
            $linha['concluiu_em'] = max($concluidas);
        } else {
            $linha['situacao'] = $mexeu ? 'em_andamento' : 'nao_iniciou';
        }
    }

    $resumo[$linha['situacao']]++;
    $pessoas[] = $linha;
}

mse_json([
    'periodo' => ['de' => $de, 'ate' => $ate],
    'resumo' => $resumo,
    'pessoas' => $pessoas,
    // Falha geral da API de ficha (sem token, fora do ar): a lista sai sem os dados extras.
    'ficha_erro' => $fichaErro,
]);
