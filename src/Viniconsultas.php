<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';
require_once __DIR__ . '/Admitidos.php';

/**
 * viniconsultas: tabela de consulta (só leitura) dos contratados recentes e
 * da prova de que concluíram, ou não, a integração na MSE Academy.
 *
 *   viniconsultas          um registro por pessoa (resumo)
 *   viniconsultas_eventos  o log: login, logout, saida, bau_aberto
 *
 * Migração 032. Se não rodou, as tabelas são criadas aqui sozinhas.
 */

/** Cria as duas tabelas se faltarem (cópia da migração 032). false = não deu. */
function mse_garantir_viniconsultas(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $sql = file_get_contents(__DIR__ . '/../migrations/032_viniconsultas.sql');
        $sql = preg_replace('#/\*.*?\*/#s', '', (string) $sql);
        foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $comando) {
            $pdo->exec($comando);
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[viniconsultas] Não consegui criar as tabelas (rode a migração 032): ' . $e->getMessage());
        return $ok = false;
    }
}

/**
 * Grava um acontecimento no log (login, logout, saida, bau_aberto) com a data
 * e a hora do banco (horário de Brasília). Nunca lança erro: o log não pode
 * derrubar login, logout nem a abertura do baú.
 *
 * @param array|null $detalhe vira JSON na coluna "detalhe"
 */
function mse_vini_registrar_evento(int $userId, string $tipo, ?array $detalhe = null): void
{
    try {
        if ($userId <= 0) {
            return;
        }
        $pdo = mse_db();
        if (!mse_garantir_viniconsultas($pdo)) {
            return;
        }
        $st = $pdo->prepare('SELECT email, cpf FROM users WHERE id = ?');
        $st->execute([$userId]);
        $u = $st->fetch() ?: ['email' => null, 'cpf' => null];
        $cpf = preg_replace('/\D/', '', (string) $u['cpf']);
        $pdo->prepare(
            'INSERT IGNORE INTO viniconsultas_eventos (user_id, cpf, email, tipo, evento_em, detalhe)
             VALUES (?, ?, ?, ?, NOW(), ?)'
        )->execute([
            $userId, strlen($cpf) === 11 ? $cpf : null, $u['email'] ?: null, $tipo,
            $detalhe ? json_encode($detalhe, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
        error_log('[viniconsultas] evento ' . $tipo . ': ' . $e->getMessage());
    }
}

/**
 * Os cursos obrigatórios da integração de uma pessoa e o que ela fez em cada
 * um (status, % assistido, data de conclusão, acertos nas perguntas).
 * Sem $userId (quem nunca entrou na Academy), lista os obrigatórios
 * publicados, todos "nao_iniciado".
 *
 * @return array<int, array<string, mixed>>
 */
function mse_vini_cursos_obrigatorios(PDO $pdo, ?int $userId): array
{
    if ($userId) {
        $ids = array_column(array_filter(mse_aulas_da_trilha($pdo, $userId), static fn($c) => $c['obrigatorio']), 'id');
    } else {
        $temObrig = mse_tem_coluna($pdo, 'courses', 'obrigatorio');
        $ids = array_map('intval', $pdo->query(
            "SELECT id FROM courses WHERE type = 'onboarding' AND is_published = 1"
            . ($temObrig ? ' AND obrigatorio = 1' : '') . ' ORDER BY order_index ASC, id ASC'
        )->fetchAll(PDO::FETCH_COLUMN));
    }
    if (!$ids) {
        return [];
    }
    $marc = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare(
        "SELECT c.id, c.title, p.status, p.watched_pct, p.completed_at,
                (SELECT COUNT(DISTINCT t.question_id) FROM quiz_attempts t JOIN quiz_questions qq ON qq.id = t.question_id
                  WHERE qq.course_id = c.id AND t.user_id = ? AND t.is_correct = 1) AS acertos,
                (SELECT COUNT(*) FROM quiz_questions qq2 WHERE qq2.course_id = c.id) AS perguntas
         FROM courses c
         LEFT JOIN user_course_progress p ON p.course_id = c.id AND p.user_id = ?
         WHERE c.id IN ({$marc})
         ORDER BY c.order_index ASC, c.id ASC"
    );
    $st->execute(array_merge([(int) $userId, (int) $userId], array_values($ids)));
    return array_map(static fn($l) => [
        'curso' => $l['title'],
        'status' => $l['status'] ?: 'nao_iniciado',
        'percentual_assistido' => $l['watched_pct'] !== null ? (int) $l['watched_pct'] : 0,
        'concluido_em' => $l['completed_at'],
        'perguntas_certas' => (int) $l['acertos'],
        'perguntas' => (int) $l['perguntas'],
    ], $st->fetchAll());
}

/**
 * A prova do momento: se a pessoa concluiu a integração AGORA (conferido no
 * banco, nunca pelo navegador), com a lista de cursos e uma frase pronta.
 *
 * @return array{concluida: bool, obrigatorias: int, concluidas: int, cursos: array, prova: string}
 */
function mse_vini_prova_integracao(PDO $pdo, int $userId): array
{
    $cursos = mse_vini_cursos_obrigatorios($pdo, $userId);
    $total = count($cursos);
    $feitos = count(array_filter($cursos, static fn($c) => $c['status'] === 'concluido'));
    $concluida = $total > 0 && $feitos === $total;
    $quando = (string) $pdo->query('SELECT DATE_FORMAT(NOW(), "%d/%m/%Y às %H:%i")')->fetchColumn();
    $prova = $concluida
        ? "Em {$quando}, a MSE Academy confirma que todos os {$total} cursos obrigatórios da integração estão concluídos."
        : "Em {$quando}, a MSE Academy registra {$feitos} de {$total} cursos obrigatórios da integração concluídos: a integração NÃO está completa.";
    return ['concluida' => $concluida, 'obrigatorias' => $total, 'concluidas' => $feitos, 'cursos' => $cursos, 'prova' => $prova];
}

/**
 * Atualiza viniconsultas com as pessoas contratadas (a lista vem da
 * sincronização: cada item já traz nome, CPF, e-mail, admissão, user_id e a
 * situação da integração).
 *
 * Também completa o log com o histórico de logins que já existia
 * (auth_tokens), só para o período anterior ao início do log em tempo real,
 * para nenhum login ser contado duas vezes.
 *
 * @return int quantas linhas gravou
 */
function mse_viniconsultas_gravar(PDO $pdo, array $pessoas, string $verificadoEm): int
{
    if (!mse_garantir_viniconsultas($pdo)) {
        return 0;
    }
    // Logins de antes do log existir: só os anteriores ao primeiro login registrado em tempo real.
    $corte = $pdo->query(
        "SELECT MIN(evento_em) FROM viniconsultas_eventos WHERE tipo = 'login' AND (detalhe IS NULL OR detalhe NOT LIKE '%historico%')"
    )->fetchColumn();
    $historico = $pdo->prepare(
        "INSERT IGNORE INTO viniconsultas_eventos (user_id, cpf, email, tipo, evento_em, detalhe)
         SELECT t.user_id, ?, ?, 'login', t.created_at, '{\"origem\":\"historico\"}'
         FROM auth_tokens t WHERE t.user_id = ?" . ($corte ? ' AND t.created_at < ?' : '')
    );
    $resumoEventos = $pdo->prepare(
        "SELECT tipo, COUNT(*) AS n, MIN(evento_em) AS primeiro, MAX(evento_em) AS ultimo
         FROM viniconsultas_eventos WHERE user_id = ? GROUP BY tipo"
    );
    $ultimoBau = $pdo->prepare(
        "SELECT detalhe FROM viniconsultas_eventos WHERE user_id = ? AND tipo = 'bau_aberto' ORDER BY evento_em DESC LIMIT 1"
    );
    $upsert = $pdo->prepare(
        'INSERT INTO viniconsultas
           (chave, nome, cpf, email, cargo, departamento, data_admissao, user_id, entrou_na_academy, primeiro_login, ultimo_login,
            total_logins, ultimo_logout, ultima_saida, situacao_integracao, integracao_concluida, integracao_concluida_em,
            aulas_obrigatorias, aulas_concluidas, cursos_obrigatorios, bau_vezes, bau_primeira_abertura, bau_ultima_abertura,
            bau_prova, verificado_em)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE
           nome = VALUES(nome), cpf = VALUES(cpf), email = VALUES(email), cargo = VALUES(cargo), departamento = VALUES(departamento),
           user_id = VALUES(user_id), entrou_na_academy = VALUES(entrou_na_academy), primeiro_login = VALUES(primeiro_login),
           ultimo_login = VALUES(ultimo_login), total_logins = VALUES(total_logins), ultimo_logout = VALUES(ultimo_logout),
           ultima_saida = VALUES(ultima_saida), situacao_integracao = VALUES(situacao_integracao),
           integracao_concluida = VALUES(integracao_concluida), integracao_concluida_em = VALUES(integracao_concluida_em),
           aulas_obrigatorias = VALUES(aulas_obrigatorias), aulas_concluidas = VALUES(aulas_concluidas),
           cursos_obrigatorios = VALUES(cursos_obrigatorios), bau_vezes = VALUES(bau_vezes),
           bau_primeira_abertura = VALUES(bau_primeira_abertura), bau_ultima_abertura = VALUES(bau_ultima_abertura),
           bau_prova = VALUES(bau_prova), verificado_em = VALUES(verificado_em)'
    );

    $gravadas = 0;
    foreach ($pessoas as $p) {
        $userId = !empty($p['user_id']) ? (int) $p['user_id'] : null;
        $cpf = preg_replace('/\D/', '', (string) ($p['cpf'] ?? ''));

        $ev = ['login' => null, 'logout' => null, 'saida' => null, 'bau_aberto' => null];
        $bauProva = null;
        if ($userId) {
            $args = [strlen($cpf) === 11 ? $cpf : null, $p['email'] ?? null, $userId];
            if ($corte) {
                $args[] = $corte;
            }
            $historico->execute($args);
            $resumoEventos->execute([$userId]);
            foreach ($resumoEventos->fetchAll() as $l) {
                $ev[$l['tipo']] = $l;
            }
            if ($ev['bau_aberto']) {
                $ultimoBau->execute([$userId]);
                $d = json_decode((string) $ultimoBau->fetchColumn(), true);
                $bauProva = is_array($d) ? ($d['prova'] ?? null) : null;
            }
        }

        $cursos = mse_vini_cursos_obrigatorios($pdo, $userId);
        $upsert->execute([
            mse_integracao_chave($cpf ?: null, (string) $p['nome'], (string) $p['data_admissao']),
            $p['nome'], strlen($cpf) === 11 ? $cpf : null, $p['email'] ?? null, $p['funcao'] ?? null, $p['departamento'] ?? null,
            $p['data_admissao'], $userId, $userId && $ev['login'] ? 1 : 0,
            $ev['login']['primeiro'] ?? null, $ev['login']['ultimo'] ?? null, (int) ($ev['login']['n'] ?? 0),
            $ev['logout']['ultimo'] ?? null, $ev['saida']['ultimo'] ?? null,
            !empty($p['dispensado']) ? 'dispensado' : $p['situacao'],
            $p['situacao'] === 'concluiu' ? 1 : 0, $p['concluiu_em'] ?? null,
            (int) ($p['obrigatorias'] ?? count($cursos)), (int) ($p['concluidas'] ?? 0),
            json_encode($cursos, JSON_UNESCAPED_UNICODE),
            (int) ($ev['bau_aberto']['n'] ?? 0), $ev['bau_aberto']['primeiro'] ?? null, $ev['bau_aberto']['ultimo'] ?? null,
            $bauProva, $verificadoEm,
        ]);
        $gravadas++;
    }
    return $gravadas;
}
