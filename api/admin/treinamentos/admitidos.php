<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';
require_once __DIR__ . '/../../../src/IntegracaoApi.php';

/**
 * Relatório de admitidos: quem a API de integração do RH diz que foi
 * admitido no período, e se fez a integração na Academy.
 *
 * Cruza pelo CPF (users.cpf, gravado no login a partir da ficha do
 * Portal); sem CPF, pelo nome completo. A integração conta como feita
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

// Quem já usa a Academy, por CPF e por nome.
$porCpf = [];
$porNome = [];
foreach ($pdo->query('SELECT id, name, email, cpf, last_access_date FROM users WHERE active = 1') as $u) {
    $cpf = preg_replace('/\D/', '', (string) $u['cpf']);
    if ($cpf !== '') {
        $porCpf[$cpf] = $u;
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
foreach ($admitidos as $a) {
    $usuario = null;
    $cruzouPor = null;
    if ($a['cpf'] && isset($porCpf[$a['cpf']])) {
        $usuario = $porCpf[$a['cpf']];
        $cruzouPor = 'cpf';
    } else {
        $mesmos = $porNome[mse_normalize_text($a['nome'])] ?? [];
        if (count($mesmos) === 1) { // homônimo: melhor não chutar
            $usuario = $mesmos[0];
            $cruzouPor = 'nome';
        }
    }

    $linha = $a + [
        'dias_desde_admissao' => (int) floor((strtotime($hoje) - strtotime($a['data_admissao'])) / 86400),
        'user_id' => null, 'email' => null, 'cruzou_por' => $cruzouPor, 'ultimo_acesso' => null,
        'obrigatorias' => 0, 'concluidas' => 0, 'concluiu_em' => null,
    ];

    if ($usuario === null) {
        $linha['situacao'] = 'sem_acesso';
    } else {
        $userId = (int) $usuario['id'];
        $linha['user_id'] = $userId;
        $linha['email'] = $usuario['email'];
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
]);
