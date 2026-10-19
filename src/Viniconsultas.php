<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';
require_once __DIR__ . '/Admitidos.php';

/**
 * consultasmia: tabela de consulta (só leitura) dos contratados recentes e
 * da prova de que concluíram, ou não, a integração na MSE Academy.
 *
 *   consultasmia    uma linha por aula obrigatória concluída de cada pessoa
 *                   (nome, cpf, email, quantas concluiu, aula, assunto,
 *                   descrição, palavras-chave)
 *
 *   consultasmia_2  resumo por pessoa (1 linha por contratado): dados da ficha,
 *                   logins, situação da integração e as aulas obrigatórias
 *
 * O log de eventos (login, vídeo, baú) que já existiu em consultasmia_2 foi
 * desligado.
 *
 * Migração 032. Se não rodou, a tabela é criada aqui sozinha.
 */

/** Cria a tabela se faltar (cópia da migração 032). false = não deu. */
function mse_garantir_viniconsultas(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        // Nome antigo (viniconsultas / viniconsultas_eventos) -> consultasmia / consultasmia_2.
        // RENAME TABLE mantém todos os dados e só roda se o nome novo ainda não existe.
        $existe = static function (string $t) use ($pdo): bool {
            $q = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $q->execute([$t]);
            return $q->fetchColumn() !== false;
        };
        foreach (['viniconsultas' => 'consultasmia'] as $velho => $novo) {
            if ($existe($velho) && !$existe($novo)) {
                $pdo->exec("RENAME TABLE {$velho} TO {$novo}");
            }
        }
        // Formato antigo (1 linha por pessoa, com situacao_integracao): renomeia para
        // consultasmia_antiga — nada é apagado — e o formato novo é criado abaixo.
        $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia' AND COLUMN_NAME = 'situacao_integracao'");
        $col->execute();
        if ($col->fetchColumn() !== false) {
            $destino = 'consultasmia_antiga';
            for ($n = 2; $existe($destino); $n++) {
                $destino = 'consultasmia_antiga_' . $n;
            }
            $pdo->exec("RENAME TABLE consultasmia TO {$destino}");
        }
        // consultasmia_2 antiga era o log de eventos (tem a coluna "tipo"): preserva, renomeando.
        $log = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia_2' AND COLUMN_NAME = 'tipo'");
        $log->execute();
        if ($log->fetchColumn() !== false) {
            $destino = 'consultasmia_2_log_antigo';
            for ($n = 2; $existe($destino); $n++) {
                $destino = 'consultasmia_2_log_antigo_' . $n;
            }
            $pdo->exec("RENAME TABLE consultasmia_2 TO {$destino}");
        }
        $sql = file_get_contents(__DIR__ . '/../migrations/032_viniconsultas.sql');
        $sql = preg_replace('#/\*.*?\*/#s', '', (string) $sql);
        foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $comando) {
            $pdo->exec($comando);
        }
        // Tempo de cada vídeo (coluna da migração 044): criada aqui se faltar.
        $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia' AND COLUMN_NAME = 'tempo_video'");
        $col->execute();
        if ($col->fetchColumn() === false) {
            $pdo->exec('ALTER TABLE consultasmia ADD COLUMN tempo_video VARCHAR(12) NULL AFTER palavras_chave');
        }
        // Tipo da aula (obrigatória ou recomendada) e se a pessoa concluiu (migração 048).
        $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia' AND COLUMN_NAME = 'tipo_aula'");
        $col->execute();
        if ($col->fetchColumn() === false) {
            $pdo->exec('ALTER TABLE consultasmia ADD COLUMN tipo_aula VARCHAR(12) NULL AFTER aula, ADD COLUMN concluida TINYINT(1) NULL AFTER tipo_aula');
            // Linhas antigas eram todas aulas obrigatórias concluídas.
            $pdo->exec("UPDATE consultasmia SET tipo_aula = 'obrigatoria', concluida = 1 WHERE aula IS NOT NULL");
        }
        // Id da aula em cada linha (migração 049): é o que mantém o id da linha estável.
        $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia' AND COLUMN_NAME = 'course_id'");
        $col->execute();
        if ($col->fetchColumn() === false) {
            $pdo->exec('ALTER TABLE consultasmia ADD COLUMN course_id INT UNSIGNED NULL AFTER aulas_obrigatorias_concluidas');
            // Linhas antigas: acha a aula pelo título (as que não acharem seguem pelo nome da aula).
            $pdo->exec('UPDATE consultasmia c JOIN courses co ON co.title COLLATE utf8mb4_unicode_ci = c.aula COLLATE utf8mb4_unicode_ci SET c.course_id = co.id WHERE c.aula IS NOT NULL AND c.course_id IS NULL');
        }
        // Hora em que a pessoa começou e terminou cada vídeo (migração 045).
        $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultasmia' AND COLUMN_NAME = 'inicio_video'");
        $col->execute();
        if ($col->fetchColumn() === false) {
            $pdo->exec('ALTER TABLE consultasmia ADD COLUMN inicio_video DATETIME NULL AFTER tempo_video, ADD COLUMN fim_video DATETIME NULL AFTER inicio_video');
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[consultasmia] Não consegui criar as tabelas (rode a migração 032): ' . $e->getMessage());
        return $ok = false;
    }
}

/**
 * (Desligado.) Gravava login, entrada/saída de vídeo e abertura do baú no log
 * consultasmia_2. Mantida porque login, progresso e baú ainda a chamam; não
 * grava nada.
 */
function mse_vini_registrar_evento(int $userId, string $tipo, ?array $detalhe = null, int $cursoId = 0, ?string $curso = null, ?string $quando = null): void
{
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
    return mse_vini_cursos_por_ids($pdo, $userId, array_map('intval', $ids));
}

/**
 * Aulas obrigatórias do DEPARTAMENTO: cursos marcados em "Obrigatório para quais
 * departamentos?" que batem com o departamento da pessoa (o da ficha, o do Portal
 * em users.setor_portal ou o nome da área). Cursos de integração (type onboarding)
 * ficam de fora: eles contam como INTEGRAÇÃO, não como aula obrigatória.
 *
 * @return array<int, array<string, mixed>>
 */
function mse_vini_aulas_do_departamento(PDO $pdo, ?int $userId, ?string $deptoFicha, string $nivel = 'obrigatorias'): array
{
    require_once __DIR__ . '/Departamentos.php';
    $recomendadas = $nivel === 'recomendadas';
    $temTabelaDeptos = mse_tem_tabela($pdo, 'course_departamentos');
    // Obrigatórias dependem dos departamentos marcados; recomendadas sem departamento valem para todos.
    if (!$temTabelaDeptos && !$recomendadas) {
        return [];
    }
    $nomes = [$deptoFicha];
    if ($userId) {
        $cols = ['a.name'];
        if (mse_tem_coluna($pdo, 'users', 'setor_portal')) {
            $cols[] = 'u.setor_portal';
        }
        $st = $pdo->prepare('SELECT ' . implode(', ', $cols) . ' FROM users u LEFT JOIN areas a ON a.id = u.area_id WHERE u.id = ?');
        $st->execute([$userId]);
        $nomes = array_merge($nomes, array_values($st->fetch(PDO::FETCH_NUM) ?: []));
    }
    $chaves = [];
    foreach ($nomes as $n) {
        if (trim((string) $n) !== '') {
            $chaves[mse_departamento_chave((string) $n)] = true;
        }
    }
    $temClassif = mse_tem_coluna($pdo, 'courses', 'obrigatoriedade');
    $marc = $chaves ? implode(',', array_fill(0, count($chaves), '?')) : "''";
    if ($recomendadas) {
        // Recomendadas: marcadas "apenas recomendado", para os departamentos relacionados (ou para todos, se nenhum foi marcado).
        if (!$temClassif) {
            return [];
        }
        $existeDeptos = $temTabelaDeptos
            ? "(NOT EXISTS (SELECT 1 FROM course_departamentos d0 WHERE d0.course_id = c.id)
                OR EXISTS (SELECT 1 FROM course_departamentos d WHERE d.course_id = c.id AND d.departamento_norm IN ({$marc})))"
            : '1 = 1';
        $st = $pdo->prepare(
            "SELECT c.id FROM courses c
             WHERE c.is_published = 1 AND c.type <> 'onboarding' AND c.obrigatoriedade = 'recomendado' AND {$existeDeptos}
             ORDER BY c.order_index ASC, c.id ASC"
        );
        $st->execute($temTabelaDeptos ? array_keys($chaves) : []);
        return mse_vini_cursos_por_ids($pdo, $userId, array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN)));
    }
    // Obrigatórias: as do departamento e as "obrigatório para todos". Recomendadas ficam de fora daqui.
    $st = $pdo->prepare(
        "SELECT c.id FROM courses c
         WHERE c.is_published = 1 AND c.type <> 'onboarding' AND (
               (EXISTS (SELECT 1 FROM course_departamentos d WHERE d.course_id = c.id AND d.departamento_norm IN ({$marc}))"
        . ($temClassif ? " AND (c.obrigatoriedade IS NULL OR c.obrigatoriedade = 'departamentos'))
               OR c.obrigatoriedade = 'todos'" : ')') . "
         )
         ORDER BY c.order_index ASC, c.id ASC"
    );
    $st->execute(array_keys($chaves));
    return mse_vini_cursos_por_ids($pdo, $userId, array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN)));
}

/** O que a pessoa fez em cada curso da lista (status, % assistido, conclusão, perguntas, entradas e saídas). */
function mse_vini_cursos_por_ids(PDO $pdo, ?int $userId, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $marc = implode(',', array_fill(0, count($ids), '?'));
    $temLog = mse_tem_tabela($pdo, 'presenca_log');
    $temCheckin = mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em');
    $st = $pdo->prepare(
        "SELECT c.id, c.title, p.status, p.watched_pct, p.completed_at, " . ($temCheckin ? 'p.checkin_em' : 'NULL AS checkin_em') . ",
                (SELECT COUNT(DISTINCT t.question_id) FROM quiz_attempts t JOIN quiz_questions qq ON qq.id = t.question_id
                  WHERE qq.course_id = c.id AND t.user_id = ? AND t.is_correct = 1) AS acertos,
                (SELECT COUNT(*) FROM quiz_questions qq2 WHERE qq2.course_id = c.id) AS perguntas,
                " . ($temLog
        ? "(SELECT COUNT(*) FROM presenca_log pl WHERE pl.user_id = ? AND pl.course_id = c.id AND pl.evento = 'checkin') AS entradas,
                (SELECT MIN(pl.criado_em) FROM presenca_log pl WHERE pl.user_id = ? AND pl.course_id = c.id AND pl.evento = 'checkin') AS primeira_entrada,
                (SELECT MAX(pl.criado_em) FROM presenca_log pl WHERE pl.user_id = ? AND pl.course_id = c.id AND pl.evento = 'checkout') AS ultima_saida"
        : 'NULL AS entradas, NULL AS primeira_entrada, NULL AS ultima_saida') . "
         FROM courses c
         LEFT JOIN user_course_progress p ON p.course_id = c.id AND p.user_id = ?
         WHERE c.id IN ({$marc})
         ORDER BY c.order_index ASC, c.id ASC"
    );
    $st->execute(array_merge($temLog ? [(int) $userId, (int) $userId, (int) $userId, (int) $userId, (int) $userId] : [(int) $userId, (int) $userId], array_values($ids)));
    return array_map(static fn($l) => [
        'id' => (int) $l['id'],
        'curso' => $l['title'],
        'status' => $l['status'] ?: 'nao_iniciado',
        'percentual_assistido' => $l['watched_pct'] !== null ? (int) $l['watched_pct'] : 0,
        'concluido_em' => $l['completed_at'],
        'checkin_em' => $l['checkin_em'],
        // Entrada (check-in) e saída (check-out) deste vídeo.
        'entradas' => (int) $l['entradas'],
        'primeira_entrada' => $l['primeira_entrada'],
        'ultima_saida' => $l['ultima_saida'],
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
 * Atualiza consultasmia com as pessoas contratadas (a lista vem da
 * sincronização: cada item já traz nome, CPF, e-mail, admissão, user_id e a
 * situação da integração).
 *
 * @return int quantas linhas gravou
 */
function mse_viniconsultas_gravar(PDO $pdo, array $pessoas, string $verificadoEm): int
{
    if (!mse_garantir_viniconsultas($pdo)) {
        return 0;
    }
    // Resumo por pessoa (consultasmia_2). Logins vêm das sessões (auth_tokens).
    $sessoes = $pdo->prepare('SELECT COUNT(*) AS n, MIN(created_at) AS primeiro, MAX(created_at) AS ultimo FROM auth_tokens WHERE user_id = ?');
    $upsertResumo = $pdo->prepare(
        'INSERT INTO consultasmia_2
           (chave, nome, cpf, email, cargo, departamento, data_admissao, user_id, entrou_na_academy, primeiro_login, ultimo_login,
            total_logins, situacao_integracao, integracao_concluida, integracao_concluida_em,
            aulas_obrigatorias, aulas_concluidas, aulas_concluidas_nomes)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE
           nome = VALUES(nome), cpf = VALUES(cpf), email = VALUES(email), cargo = VALUES(cargo), departamento = VALUES(departamento),
           user_id = VALUES(user_id), entrou_na_academy = VALUES(entrou_na_academy), primeiro_login = VALUES(primeiro_login),
           ultimo_login = VALUES(ultimo_login), total_logins = VALUES(total_logins),
           situacao_integracao = VALUES(situacao_integracao),
           integracao_concluida = VALUES(integracao_concluida), integracao_concluida_em = VALUES(integracao_concluida_em),
           aulas_obrigatorias = VALUES(aulas_obrigatorias), aulas_concluidas = VALUES(aulas_concluidas),
           aulas_concluidas_nomes = VALUES(aulas_concluidas_nomes)'
    );

    $gravadas = 0;
    foreach ($pessoas as $p) {
        $userId = !empty($p['user_id']) ? (int) $p['user_id'] : null;
        $cpf = preg_replace('/\D/', '', (string) ($p['cpf'] ?? ''));
        $cpf = strlen($cpf) === 11 ? $cpf : null;

        // Aulas obrigatórias = as do departamento da pessoa e as obrigatórias para todos (integração fica de fora).
        $aulas = mse_vini_aulas_do_departamento($pdo, $userId, isset($p['departamento']) ? (string) $p['departamento'] : null);
        $recomendadas = mse_vini_aulas_do_departamento($pdo, $userId, isset($p['departamento']) ? (string) $p['departamento'] : null, 'recomendadas');
        mse_vini_gravar_linhas_pessoa($pdo, (string) $p['nome'], $cpf, $p['email'] ?? null, $aulas, $recomendadas);

        $login = ['n' => 0, 'primeiro' => null, 'ultimo' => null];
        if ($userId) {
            $sessoes->execute([$userId]);
            $login = $sessoes->fetch() ?: $login;
        }
        $feitasNomes = array_map(static fn($c) => $c['curso'], array_filter($aulas, static fn($c) => $c['status'] === 'concluido'));
        $upsertResumo->execute([
            mse_integracao_chave($cpf, (string) $p['nome'], (string) $p['data_admissao']),
            $p['nome'], $cpf, $p['email'] ?? null, $p['funcao'] ?? null, $p['departamento'] ?? null,
            $p['data_admissao'], $userId, (int) $login['n'] > 0 ? 1 : 0, $login['primeiro'], $login['ultimo'], (int) $login['n'],
            !empty($p['dispensado']) ? 'dispensado' : $p['situacao'],
            $p['situacao'] === 'concluiu' ? 1 : 0, $p['concluiu_em'] ?? null,
            count($aulas), $aulas && count($feitasNomes) === count($aulas) ? 1 : 0, $feitasNomes ? implode(' | ', $feitasNomes) : null,
        ]);
        $gravadas++;
    }
    return $gravadas;
}

/**
 * Recalcula na hora as aulas obrigatórias/concluídas da pessoa em consultasmia
 * (chamado quando ela conclui uma aula), sem esperar o "Atualizar banco".
 * Só mexe em quem já tem linha na tabela (contratados recentes) e nunca
 * lança erro: concluir a aula não pode falhar por causa disso.
 */
function mse_vini_atualizar_aulas_do_usuario(int $userId): void
{
    try {
        $pdo = mse_db();
        if ($userId <= 0 || !mse_tem_tabela($pdo, 'consultasmia') || !mse_tem_coluna($pdo, 'consultasmia', 'aulas_obrigatorias_concluidas')) {
            return;
        }
        $st = $pdo->prepare('SELECT cpf, email FROM users WHERE id = ?');
        $st->execute([$userId]);
        $u = $st->fetch();
        if (!$u) {
            return;
        }
        $cpf = preg_replace('/\D/', '', (string) $u['cpf']);
        $cpf = strlen($cpf) === 11 ? $cpf : null;
        // Só quem já está na tabela (contratados recentes), achado pelo CPF ou e-mail.
        $st = $pdo->prepare($cpf ? 'SELECT nome, email FROM consultasmia WHERE cpf = ? LIMIT 1' : 'SELECT nome, email FROM consultasmia WHERE email = ? LIMIT 1');
        $st->execute([$cpf ?: $u['email']]);
        $linha = $st->fetch();
        if (!$linha) {
            return;
        }
        mse_vini_recalcular_pessoa($pdo, $userId, (string) $linha['nome'], $cpf, $linha['email'] ?: $u['email']);
    } catch (Throwable $e) {
        error_log('[consultasmia] atualizar aulas do usuário: ' . $e->getMessage());
    }
}

/**
 * Recalcula as aulas obrigatórias de UMA pessoa e regrava as duas tabelas
 * (consultasmia: uma linha por aula concluída; consultasmia_2: resumo).
 * O departamento vem do resumo (ficha do Portal) quando existe.
 */
function mse_vini_recalcular_pessoa(PDO $pdo, ?int $userId, string $nome, ?string $cpf, ?string $email): void
{
    $depto = null;
    if (mse_tem_tabela($pdo, 'consultasmia_2') && mse_tem_coluna($pdo, 'consultasmia_2', 'aulas_concluidas_nomes')) {
        $st = $pdo->prepare($cpf ? 'SELECT departamento FROM consultasmia_2 WHERE cpf = ? LIMIT 1' : 'SELECT departamento FROM consultasmia_2 WHERE email = ? LIMIT 1');
        $st->execute([$cpf ?: $email]);
        $d = $st->fetchColumn();
        $depto = $d !== false && trim((string) $d) !== '' ? (string) $d : null;
    }
    $aulas = mse_vini_aulas_do_departamento($pdo, $userId, $depto);
    $recomendadas = mse_vini_aulas_do_departamento($pdo, $userId, $depto, 'recomendadas');
    mse_vini_gravar_linhas_pessoa($pdo, $nome, $cpf, $email, $aulas, $recomendadas);
    if ($depto !== null || ($userId && mse_tem_tabela($pdo, 'consultasmia_2'))) {
        $feitas = array_values(array_filter($aulas, static fn($c) => $c['status'] === 'concluido'));
        $where = $cpf ? 'cpf = ?' : 'email = ?';
        $pdo->prepare("UPDATE consultasmia_2 SET aulas_obrigatorias = ?, aulas_concluidas = ?, aulas_concluidas_nomes = ? WHERE {$where}")->execute([
            count($aulas), $aulas && count($feitas) === count($aulas) ? 1 : 0,
            $feitas ? implode(' | ', array_column($feitas, 'curso')) : null, $cpf ?: $email,
        ]);
    }
}

/**
 * Acha o users.id de uma pessoa da tabela (por CPF, depois por e-mail).
 */
function mse_vini_achar_usuario(PDO $pdo, ?string $cpf, ?string $email): ?int
{
    if ($cpf) {
        $st = $pdo->prepare('SELECT id FROM users WHERE cpf = ? LIMIT 1');
        $st->execute([$cpf]);
        $id = $st->fetchColumn();
        if ($id) {
            return (int) $id;
        }
    }
    if ($email) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $id = $st->fetchColumn();
        if ($id) {
            return (int) $id;
        }
    }
    return null;
}

/**
 * Recalcula as pessoas de consultasmia (só banco, sem API de fora), A CADA HORA
 * e em LOTES COM LIMITE DE TEMPO, para nunca pesar nem travar o sistema.
 *
 * Uma "passada" passa por todo mundo da tabela, pouco a pouco (continuando de
 * onde parou na chamada seguinte, por um cursor estável). Ela começa:
 *   - a cada 1 hora (desde o início da passada anterior);
 *   - ou na hora, quando uma aula muda (criar, editar, excluir, aprovar,
 *     trocar departamentos) ou na primeira vez.
 * Sem passada em andamento e antes de completar 1 hora, não faz nada.
 * (Quando a pessoa conclui uma aula, a linha dela já é atualizada na hora.)
 *
 * @param float $orcamentoSeg tempo máximo desta chamada
 * @return int quantas pessoas recalculou agora
 */
function mse_vini_recalcular_lote(PDO $pdo, float $orcamentoSeg = 5.0): int
{
    if (!mse_tem_tabela($pdo, 'consultasmia') || !mse_tem_coluna($pdo, 'consultasmia', 'aulas_obrigatorias_concluidas')) {
        return 0;
    }
    require_once __DIR__ . '/Configuracoes.php';
    if (!mse_garantir_configuracoes($pdo)) {
        return 0;
    }
    $inicio = microtime(true);

    // Começa uma passada nova? (a cada hora, ou se já tem uma pedida / em andamento)
    if (mse_config_ler($pdo, 'consultasmia_total_pendente', '0') !== '1') {
        $ultima = mse_config_ler($pdo, 'consultasmia_passada_em', '');
        $passouUmaHora = $ultima === ''
            || (int) $pdo->query("SELECT TIMESTAMPDIFF(SECOND, " . $pdo->quote($ultima) . ", NOW())")->fetchColumn() >= 3600;
        if (!$passouUmaHora) {
            return 0;
        }
        mse_config_gravar($pdo, 'consultasmia_passada_em', (string) $pdo->query('SELECT NOW()')->fetchColumn(), 'sistema');
        mse_config_gravar($pdo, 'consultasmia_total_pendente', '1', 'sistema');
        mse_config_gravar($pdo, 'consultasmia_total_cursor', '', 'sistema');
    }

    $n = 0;
    // O cursor é uma chave ESTÁVEL (CPF, ou nome sem CPF).
    $cursor = mse_config_ler($pdo, 'consultasmia_total_cursor', '');
    $st = $pdo->prepare(
        "SELECT nome, cpf, email, COALESCE(cpf, CONCAT('~', nome)) AS chave FROM consultasmia
         GROUP BY nome, cpf, email HAVING chave > ? ORDER BY chave ASC LIMIT 60"
    );
    $st->execute([$cursor]);
    $lote = $st->fetchAll();
    if (!$lote) {
        mse_config_gravar($pdo, 'consultasmia_total_pendente', '0', 'sistema');
        mse_config_gravar($pdo, 'consultasmia_total_cursor', '', 'sistema');
        return 0;
    }
    foreach ($lote as $p) {
        if (microtime(true) - $inicio > $orcamentoSeg) {
            break;
        }
        try {
            mse_vini_recalcular_pessoa($pdo, mse_vini_achar_usuario($pdo, $p['cpf'] ?: null, $p['email'] ?: null), (string) $p['nome'], $p['cpf'] ?: null, $p['email'] ?: null);
            $n++;
        } catch (Throwable $e) {
            error_log('[consultasmia] recalcular ' . $p['nome'] . ': ' . $e->getMessage());
        }
        $cursor = (string) $p['chave'];
    }
    mse_config_gravar($pdo, 'consultasmia_total_cursor', $cursor, 'sistema');
    return $n;
}

/**
 * Pede uma passada completa (uma aula mudou): o recálculo em lotes cuida dela
 * nas próximas chamadas. Barato: só marca.
 */
function mse_vini_marcar_recalculo_total(PDO $pdo): void
{
    try {
        require_once __DIR__ . '/Configuracoes.php';
        if (mse_tem_tabela($pdo, 'consultasmia') && mse_garantir_configuracoes($pdo)) {
            mse_config_gravar($pdo, 'consultasmia_total_pendente', '1', 'sistema');
            mse_config_gravar($pdo, 'consultasmia_total_cursor', '', 'sistema');
        }
    } catch (Throwable $e) {
        error_log('[consultasmia] marcar recalculo: ' . $e->getMessage());
    }
}

/**
 * Chamada de dentro de requisições comuns (progresso, presença, mudança de
 * aula): no MÁXIMO uma vez por minuto confere se há passada a fazer (a cada
 * hora, ou aula mudou) e, se houver, faz um lote CURTO (limite de poucos
 * segundos) depois de entregar a resposta. Nunca lança erro.
 *
 * @param bool $aulaMudou true quando a chamada vem de uma mudança de aula (pede passada completa)
 */
function mse_vini_recalcular_agendado(bool $aulaMudou = false): void
{
    try {
        $pdo = mse_db();
        if (!mse_tem_tabela($pdo, 'consultasmia')) {
            return;
        }
        require_once __DIR__ . '/Configuracoes.php';
        if (!mse_garantir_configuracoes($pdo)) {
            return;
        }
        if ($aulaMudou) {
            mse_vini_marcar_recalculo_total($pdo);
        }
        // Quem pegar o "turno" do minuto roda; os outros seguem sem fazer nada (UPDATE atômico).
        $pdo->exec("INSERT IGNORE INTO configuracoes (chave, valor, atualizado_em) VALUES ('consultasmia_recalculo_em', '2000-01-01 00:00:00', NOW())");
        $st = $pdo->prepare("UPDATE configuracoes SET valor = NOW(), atualizado_em = NOW() WHERE chave = 'consultasmia_recalculo_em' AND valor <= DATE_SUB(NOW(), INTERVAL 60 SECOND)");
        $st->execute();
        if ($st->rowCount() !== 1) {
            return;
        }
        // Roda depois da resposta (PHP-FPM); sem FPM roda no fim da requisição, mas com limite curto.
        register_shutdown_function(static function () use ($pdo): void {
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }
            try {
                ignore_user_abort(true);
                @set_time_limit(30);
                mse_vini_recalcular_lote($pdo, 5.0);
            } catch (Throwable $e) {
                error_log('[consultasmia] recalculo agendado: ' . $e->getMessage());
            }
        });
    } catch (Throwable $e) {
        error_log('[consultasmia] agendar recalculo: ' . $e->getMessage());
    }
}

/** Segundos -> "mm:ss" (ou "h:mm:ss" a partir de 1 hora). 0 ou vazio = sem tempo. */
function mse_vini_formatar_tempo(int $segundos): ?string
{
    if ($segundos <= 0) {
        return null;
    }
    $h = intdiv($segundos, 3600);
    $m = intdiv($segundos % 3600, 60);
    $s = $segundos % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%02d:%02d', $m, $s);
}

/**
 * Regrava as linhas da pessoa em consultasmia: uma por aula obrigatória
 * concluída (com assunto, descrição e palavras-chave da aula). Sem nenhuma
 * concluída, fica 1 linha com a aula vazia.
 *
 * @param array $aulas as aulas obrigatórias da pessoa (mse_vini_aulas_do_departamento)
 */
function mse_vini_gravar_linhas_pessoa(PDO $pdo, string $nome, ?string $cpf, ?string $email, array $aulas, array $recomendadas = []): void
{
    require_once __DIR__ . '/Assuntos.php';
    // Cria/atualiza a tabela e as colunas (tempo_video...) ANTES da transação: DDL encerraria a transação.
    if (!$pdo->inTransaction() && !mse_garantir_viniconsultas($pdo)) {
        throw new RuntimeException('consultasmia indisponível');
    }
    $feitas = array_values(array_filter($aulas, static fn($c) => $c['status'] === 'concluido'));
    $detalhes = [];
    if ($feitas || $recomendadas) {
        $ids = array_map(static fn($c) => (int) $c['id'], array_merge($feitas, $recomendadas));
        $marc = implode(',', array_fill(0, count($ids), '?'));
        $temPalavras = mse_tem_coluna($pdo, 'courses', 'palavras_chave');
        $temSeg = mse_tem_coluna($pdo, 'courses', 'duration_seconds');
        $st = $pdo->prepare('SELECT c.id, c.title, c.description, c.duration_minutes, a.name AS area_name, '
            . ($temSeg ? 'c.duration_seconds' : 'NULL AS duration_seconds') . ', '
            . ($temPalavras ? 'c.palavras_chave' : 'NULL AS palavras_chave')
            . " FROM courses c LEFT JOIN areas a ON a.id = c.area_id WHERE c.id IN ({$marc})");
        $st->execute($ids);
        foreach ($st->fetchAll() as $c) {
            $assuntos = array_column(mse_assuntos_do_curso($pdo, (int) $c['id']), 'name');
            $detalhes[(int) $c['id']] = [
                'aula' => $c['title'],
                'assunto' => $assuntos ? implode(', ', $assuntos) : $c['area_name'],
                'descricao' => $c['description'] !== '' ? $c['description'] : null,
                'palavras_chave' => $c['palavras_chave'] ?: null,
                'tempo_video' => mse_vini_formatar_tempo($c['duration_seconds'] !== null ? (int) $c['duration_seconds'] : (int) $c['duration_minutes'] * 60),
                'inicio_video' => null,
                'fim_video' => null,
            ];
        }
    }
    $jaEmTransacao = $pdo->inTransaction();
    if (!$jaEmTransacao) {
        $pdo->beginTransaction();
    }
    try {
        // Atualiza as linhas da pessoa NO LUGAR: a linha de cada aula mantém o mesmo id para sempre.
        // Só entra linha nova para aula nova e só sai linha de aula que deixou de valer.
        $quem = $pdo->prepare($cpf ? 'SELECT id, course_id, aula FROM consultasmia WHERE cpf = ? ORDER BY id' : 'SELECT id, course_id, aula FROM consultasmia WHERE cpf IS NULL AND nome = ? ORDER BY id');
        $quem->execute([$cpf ?: $nome]);
        $existentes = $quem->fetchAll();
        $usadas = [];
        $acha = static function (?int $cid, ?string $aula) use (&$existentes, &$usadas): ?int {
            foreach ($existentes as $e) {
                if (isset($usadas[(int) $e['id']])) {
                    continue;
                }
                $mesmo = $cid !== null
                    ? ((int) $e['course_id'] === $cid || ($e['course_id'] === null && $e['aula'] === $aula))
                    : ($e['course_id'] === null && $e['aula'] === null);
                if ($mesmo) {
                    return (int) $e['id'];
                }
            }
            return null;
        };
        $upd = $pdo->prepare(
            'UPDATE consultasmia SET nome = ?, cpf = ?, email = ?, aulas_obrigatorias_concluidas = ?, course_id = ?, aula = ?, tipo_aula = ?, concluida = ?,
                    assunto = ?, descricao = ?, palavras_chave = ?, tempo_video = ?, inicio_video = ?, fim_video = ? WHERE id = ?'
        );
        $ins = $pdo->prepare(
            'INSERT INTO consultasmia (nome, cpf, email, aulas_obrigatorias_concluidas, course_id, aula, tipo_aula, concluida, assunto, descricao, palavras_chave, tempo_video, inicio_video, fim_video)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        // Início = primeira vez que abriu o vídeo; fim = quando concluiu.
        $montar = static function (array $c, string $tipo) use ($detalhes): array {
            $d = $detalhes[(int) $c['id']] ?? ['aula' => $c['curso'], 'assunto' => null, 'descricao' => null, 'palavras_chave' => null, 'tempo_video' => null];
            $feito = ($c['status'] ?? '') === 'concluido';
            $d['course_id'] = (int) $c['id'];
            $d['tipo'] = $tipo;
            $d['concluida'] = $feito ? 1 : 0;
            $fim = $feito ? ($c['concluido_em'] ?? null) : null;
            // Início = a primeira abertura registrada. Se só existe registro DEPOIS da conclusão
            // (concluiu antes de o registro existir e reabriu depois), o início real é desconhecido: fica vazio.
            $candidatos = array_filter([$c['primeira_entrada'] ?? null, $c['checkin_em'] ?? null]);
            $inicio = $candidatos ? min($candidatos) : null;
            if ($fim !== null && $inicio !== null && $inicio > $fim) {
                $inicio = null;
            }
            $d['inicio_video'] = $inicio;
            $d['fim_video'] = $fim;
            return $d;
        };
        // Obrigatórias: só as concluídas (uma linha por aula). Recomendadas: todas, feitas ou não.
        $linhas = array_merge(
            array_map(static fn($c) => $montar($c, 'obrigatoria'), $feitas),
            array_map(static fn($c) => $montar($c, 'recomendada'), $recomendadas)
        );
        if (!$linhas) {
            $linhas = [['course_id' => null, 'aula' => null, 'tipo' => null, 'concluida' => null, 'assunto' => null, 'descricao' => null, 'palavras_chave' => null, 'tempo_video' => null, 'inicio_video' => null, 'fim_video' => null]];
        }
        foreach ($linhas as $l) {
            // 1 nas linhas de aula OBRIGATÓRIA concluída; 0 nas recomendadas e na linha sem aula.
            $valores = [$nome, $cpf, $email, $l['tipo'] === 'obrigatoria' ? 1 : 0, $l['course_id'], $l['aula'], $l['tipo'], $l['concluida'], $l['assunto'], $l['descricao'], $l['palavras_chave'], $l['tempo_video'], $l['inicio_video'], $l['fim_video']];
            $id = $acha($l['course_id'], $l['aula']);
            if ($id !== null) {
                $usadas[$id] = true;
                $upd->execute(array_merge($valores, [$id]));
            } else {
                $ins->execute($valores);
            }
        }
        // O que sobrou (aula que saiu da lista, excluída, ou a linha vazia que não vale mais) é apagado.
        $apagar = $pdo->prepare('DELETE FROM consultasmia WHERE id = ?');
        foreach ($existentes as $e) {
            if (!isset($usadas[(int) $e['id']])) {
                $apagar->execute([(int) $e['id']]);
            }
        }
        if (!$jaEmTransacao) {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if (!$jaEmTransacao && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
