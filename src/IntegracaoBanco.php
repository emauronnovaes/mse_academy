<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';
require_once __DIR__ . '/IntegracaoApi.php';
require_once __DIR__ . '/PortalFichaApi.php';
require_once __DIR__ . '/Admitidos.php';
require_once __DIR__ . '/Viniconsultas.php';

/**
 * Grava no banco, para outro sistema consultar, quem foi admitido e se fez a
 * integração (tabela integracao_admitidos e view v_integracao_pendentes —
 * migração 027).
 *
 * O outro sistema só lê. Cada linha leva a "prova": um texto pronto com a
 * data da verificação e, aula por aula, o status e a data (detalhe_aulas).
 */

/** Cria a tabela e a view se a migração 027 ainda não rodou. false = não deu. */
function mse_garantir_tabela_integracao(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $sql = file_get_contents(__DIR__ . '/../migrations/027_integracao_pendentes.sql');
        // O arquivo tem 2 comandos depois do comentário: a tabela e a view.
        $sql = preg_replace('#/\*.*?\*/#s', '', (string) $sql);
        foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $comando) {
            $pdo->exec($comando);
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[integracao_admitidos] Não consegui criar (rode a migração 027): ' . $e->getMessage());
        return $ok = false;
    }
}

function mse_data_br(?string $v, bool $comHora = false): string
{
    $t = $v ? strtotime($v) : false;
    return $t ? date($comHora ? 'd/m/Y \à\s H:i' : 'd/m/Y', $t) : '';
}

/**
 * Atualiza a tabela: busca os admitidos de $de a $ate na API de integração,
 * soma os que já estavam pendentes na tabela (pra continuarem sendo
 * conferidos mesmo que tenham saído do período), completa com a ficha e
 * regrava a situação de cada um.
 *
 * @return array{total: int, pendentes: int, concluiram: int, dispensados: int, novos: int, aviso: ?string}
 */
function mse_sincronizar_integracao(PDO $pdo, string $de, string $ate): array
{
    if (!mse_garantir_tabela_integracao($pdo)) {
        throw new RuntimeException('Não consegui criar a tabela. Rode a migração 027 no banco (migrations/027_integracao_pendentes.sql).');
    }
    $agora = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')));
    $hoje = $agora->format('Y-m-d');
    $verificadoEm = $agora->format('Y-m-d H:i:s');

    $aviso = null;
    $admitidos = mse_integracao_admitidos($de, $ate, $aviso);

    // Quem já estava pendente na tabela continua sendo conferido.
    $jaNaLista = [];
    foreach ($admitidos as $a) {
        foreach (mse_integracao_chaves($a['cpf'], $a['nome'], $a['data_admissao']) as $c) {
            $jaNaLista[$c] = true;
        }
    }
    // Todas as chaves já gravadas (pra a mesma pessoa nunca virar duas linhas).
    $todasChaves = array_flip($pdo->query('SELECT chave FROM integracao_admitidos')->fetchAll(PDO::FETCH_COLUMN));
    $existentes = [];
    foreach ($pdo->query("SELECT chave, nome, cpf, data_admissao, cargo, obra, empresa, vinculo FROM integracao_admitidos WHERE situacao <> 'concluiu'") as $l) {
        $existentes[$l['chave']] = true;
        if (!isset($jaNaLista[$l['chave']])) {
            $admitidos[] = [
                'nome' => $l['nome'], 'cpf' => $l['cpf'], 'data_admissao' => $l['data_admissao'],
                'funcao' => $l['cargo'], 'obra' => $l['obra'], 'vinculo' => $l['vinculo'], 'empresa' => $l['empresa'],
            ];
        }
    }

    // Fichas de funcionário, em partes de 40 pra não estourar o tempo.
    $fichas = [];
    foreach (array_chunk($admitidos, 40, true) as $parte) {
        $fichas += mse_portal_ficha_buscar_varias(array_map(
            static fn($a) => ['cpf' => $a['cpf'], 'nome' => $a['nome']],
            $parte
        ));
    }
    $montado = mse_admitidos_montar($pdo, $admitidos, $fichas, $hoje);

    // Consulta montada na hora, com os ids da trilha de cada pessoa (inteiros).
    $detalheSql = 'SELECT c.id, c.title, p.status, p.watched_pct, p.completed_at
         FROM courses c
         LEFT JOIN user_course_progress p ON p.course_id = c.id AND p.user_id = ?
         WHERE c.id IN (%s)
         ORDER BY c.order_index ASC, c.id ASC';
    $upsert = $pdo->prepare(
        'INSERT INTO integracao_admitidos
           (chave, nome, cpf, email, telefone, cargo, departamento, obra, empresa, vinculo, gerente_nome, gerente_email,
            data_admissao, user_id, situacao, aulas_obrigatorias, aulas_concluidas, ultimo_acesso, concluiu_em,
            prova, detalhe_aulas, primeira_verificacao, verificado_em)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE
           nome = VALUES(nome), cpf = VALUES(cpf), email = VALUES(email), telefone = VALUES(telefone), cargo = VALUES(cargo),
           departamento = VALUES(departamento), obra = VALUES(obra), empresa = VALUES(empresa), vinculo = VALUES(vinculo),
           gerente_nome = VALUES(gerente_nome), gerente_email = VALUES(gerente_email), user_id = VALUES(user_id),
           situacao = VALUES(situacao), aulas_obrigatorias = VALUES(aulas_obrigatorias), aulas_concluidas = VALUES(aulas_concluidas),
           ultimo_acesso = VALUES(ultimo_acesso), concluiu_em = VALUES(concluiu_em), prova = VALUES(prova),
           detalhe_aulas = VALUES(detalhe_aulas), verificado_em = VALUES(verificado_em)'
    );

    $contagem = ['total' => 0, 'pendentes' => 0, 'concluiram' => 0, 'dispensados' => 0, 'novos' => 0, 'aviso' => $aviso];
    $processadas = [];
    foreach ($montado['pessoas'] as $p) {
        // Usa a chave que já existe no banco (por CPF ou por nome); sem nenhuma, a do CPF.
        [$chaveCpf, $chaveNome] = mse_integracao_chaves($p['cpf'], $p['nome'], $p['data_admissao']);
        $chave = isset($todasChaves[$chaveCpf]) ? $chaveCpf : (isset($todasChaves[$chaveNome]) ? $chaveNome : $chaveCpf);
        if (isset($processadas[$chave])) {
            continue; // a mesma pessoa veio da API e também da lista de pendentes
        }
        $processadas[$chave] = true;

        // Aula por aula, da trilha DESSA pessoa (com sorteio cada um tem a sua).
        $aulas = [];
        if ($p['user_id']) {
            $ids = array_column(array_filter(mse_aulas_da_trilha($pdo, (int) $p['user_id']), static fn($c) => $c['obrigatorio']), 'id');
            if ($ids) {
                $st = $pdo->prepare(sprintf($detalheSql, implode(',', array_map('intval', $ids))));
                $st->execute([(int) $p['user_id']]);
                foreach ($st->fetchAll() as $l) {
                    $aulas[] = [
                        'aula' => $l['title'],
                        'status' => $l['status'] ?: 'nao_iniciado',
                        'percentual_assistido' => $l['watched_pct'] !== null ? (int) $l['watched_pct'] : 0,
                        'concluida_em' => $l['completed_at'],
                    ];
                }
            }
        }

        $quando = mse_data_br($verificadoEm, true);
        $acesso = $p['ultimo_acesso'] ? 'último acesso à Academy em ' . mse_data_br($p['ultimo_acesso']) : 'nunca acessou a Academy';
        $situacaoGravada = !empty($p['dispensado']) ? 'dispensado' : $p['situacao'];
        if (!empty($p['dispensado'])) {
            $prova = 'Dispensado da integração' . (!empty($p['dispensa']['por']) ? ' por ' . $p['dispensa']['por'] : '')
                . (!empty($p['dispensa']['em']) ? ' em ' . mse_data_br($p['dispensa']['em']) : '') . '.'
                . (!empty($p['dispensa']['motivo']) ? ' Motivo: ' . $p['dispensa']['motivo'] . '.' : '');
        } elseif ($p['situacao'] === 'sem_acesso') {
            $prova = "Admitido em " . mse_data_br($p['data_admissao']) . ". Em {$quando}, a MSE Academy não tem registro de acesso dessa pessoa: a integração não foi iniciada.";
        } elseif ($p['situacao'] === 'concluiu') {
            $prova = "Concluiu a integração" . ($p['concluiu_em'] ? ' em ' . mse_data_br($p['concluiu_em']) : '') . ".";
        } else {
            $prova = "Admitido em " . mse_data_br($p['data_admissao']) . ". Em {$quando}, a MSE Academy registra {$p['concluidas']} de {$p['obrigatorias']} aulas obrigatórias da integração concluídas; {$acesso}.";
        }

        $upsert->execute([
            $chave, $p['nome'], $p['cpf'] ?: null, $p['email'] ?: null, $p['telefone'] ?? null, $p['funcao'] ?: null,
            $p['departamento'] ?? null, $p['obra'] ?: null, $p['empresa'] ?: null, $p['vinculo'] ?: null,
            $p['gerente_nome'] ?? null, $p['gerente_email'] ?? null, $p['data_admissao'], $p['user_id'], $situacaoGravada,
            (int) $p['obrigatorias'], (int) $p['concluidas'], $p['ultimo_acesso'] ?: null, $p['concluiu_em'] ?: null,
            $prova, $aulas ? json_encode($aulas, JSON_UNESCAPED_UNICODE) : null, $verificadoEm, $verificadoEm,
        ]);

        $contagem['total']++;
        if ($situacaoGravada === 'dispensado') {
            $contagem['dispensados']++;
        } elseif ($p['situacao'] === 'concluiu') {
            $contagem['concluiram']++;
        } else {
            $contagem['pendentes']++;
        }
        if (!isset($todasChaves[$chave])) {
            $contagem['novos']++;
        }
    }
    // viniconsultas: a mesma lista de contratados, com login, baú e cursos obrigatórios.
    $contagem['viniconsultas'] = mse_viniconsultas_gravar($pdo, $montado['pessoas'], $verificadoEm);
    return $contagem;
}
