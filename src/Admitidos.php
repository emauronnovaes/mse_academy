<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';

function mse_integracao_chave(?string $cpf, string $nome, string $dataAdmissao): string
{
    $cpf = preg_replace('/\D/', '', (string) $cpf);
    return strlen($cpf) === 11 ? 'cpf:' . $cpf : 'nome:' . sha1(mse_normalize_text($nome) . '|' . $dataAdmissao);
}

/** As duas chaves possíveis de uma pessoa: pelo CPF (se houver) e pelo nome+data. */
function mse_integracao_chaves(?string $cpf, string $nome, string $dataAdmissao): array
{
    return [
        mse_integracao_chave($cpf, $nome, $dataAdmissao),
        mse_integracao_chave(null, $nome, $dataAdmissao),
    ];
}

/** Garante a tabela integracao_dispensados (migração 028). */
function mse_garantir_tabela_dispensados(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS integracao_dispensados (
               chave VARCHAR(64) NOT NULL, nome VARCHAR(200) NOT NULL, cpf VARCHAR(11) NULL,
               data_admissao DATE NOT NULL, motivo VARCHAR(300) NULL, dispensado_por VARCHAR(200) NULL,
               dispensado_em DATETIME NOT NULL, PRIMARY KEY (chave)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[dispensados] Não consegui criar a tabela (rode a migração 028): ' . $e->getMessage());
        return $ok = false;
    }
}

/** Dispensados já gravados, por chave. Sem a tabela, nenhum. */
function mse_dispensados_mapa(PDO $pdo): array
{
    $st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $st->execute(['integracao_dispensados']);
    if ($st->fetchColumn() === false) {
        return [];
    }
    $mapa = [];
    foreach ($pdo->query('SELECT chave, motivo, dispensado_por, dispensado_em FROM integracao_dispensados') as $l) {
        $mapa[$l['chave']] = $l;
    }
    return $mapa;
}

/**
 * Monta as linhas do relatório de admitidos: cada admitido (vindo da API de
 * integração), completado com a ficha de funcionário (se já foi buscada),
 * cruzado com quem usa a Academy e com a situação da integração.
 *
 * $fichas: mesma chave de $admitidos => ['ficha' => ?array, 'bruta' => ?array]
 *          (vazio = ainda sem ficha; a linha sai com "ficha_pendente").
 *
 * @return array{pessoas: array, resumo: array}
 */
function mse_admitidos_montar(PDO $pdo, array $admitidos, array $fichas, string $hoje): array
{
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

$dispensas = mse_dispensados_mapa($pdo);
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
        'ficha_pendente' => !array_key_exists($i, $fichas), 'user_id' => null, 'cruzou_por' => $cruzouPor, 'ultimo_acesso' => null,
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

    // Dispensado da integração por um admin: continua na lista, marcado.
    $linha['dispensado'] = false;
    $linha['dispensa'] = null;
    foreach (mse_integracao_chaves($linha['cpf'], $linha['nome'], $linha['data_admissao']) as $c) {
        if (isset($dispensas[$c])) {
            $linha['dispensado'] = true;
            $linha['dispensa'] = [
                'motivo' => $dispensas[$c]['motivo'],
                'por' => $dispensas[$c]['dispensado_por'],
                'em' => $dispensas[$c]['dispensado_em'],
            ];
            break;
        }
    }

    $resumo[$linha['situacao']]++;
    $pessoas[] = $linha;
}

    return ['pessoas' => $pessoas, 'resumo' => $resumo];
}
