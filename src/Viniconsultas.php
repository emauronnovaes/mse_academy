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
 *   consultasmia_2  o log: login, video_entrada, video_saida, bau_aberto
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
        // Nome antigo (viniconsultas / viniconsultas_eventos) -> consultasmia / consultasmia_2.
        // RENAME TABLE mantém todos os dados e só roda se o nome novo ainda não existe.
        $existe = static function (string $t) use ($pdo): bool {
            $q = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $q->execute([$t]);
            return $q->fetchColumn() !== false;
        };
        foreach (['viniconsultas' => 'consultasmia', 'viniconsultas_eventos' => 'consultasmia_2'] as $velho => $novo) {
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
        $sql = file_get_contents(__DIR__ . '/../migrations/032_viniconsultas.sql');
        $sql = preg_replace('#/\*.*?\*/#s', '', (string) $sql);
        foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $comando) {
            $pdo->exec($comando);
        }
        // Quem já rodou a primeira versão da migração (sem o log por vídeo) ganha as colunas novas.
        $tem = static function (string $tabela, string $coluna) use ($pdo): bool {
            $st = $pdo->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $st->execute([$tabela, $coluna]);
            return $st->fetchColumn() !== false;
        };
        if (!$tem('consultasmia_2', 'curso_id')) {
            $pdo->exec('ALTER TABLE consultasmia_2 ADD COLUMN curso_id INT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN curso VARCHAR(200) NULL');
            $pdo->exec('ALTER TABLE consultasmia_2 DROP INDEX uq_viniconsultas_eventos, ADD UNIQUE KEY uq_viniconsultas_eventos (user_id, tipo, evento_em, curso_id)');
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[consultasmia] Não consegui criar as tabelas (rode a migração 032): ' . $e->getMessage());
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
function mse_vini_registrar_evento(int $userId, string $tipo, ?array $detalhe = null, int $cursoId = 0, ?string $curso = null, ?string $quando = null): void
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
        // $quando: a hora exata do registro original (check-in/out de vídeo), para
        // o espelho e a cópia do histórico serem o MESMO registro (sem duplicar).
        $pdo->prepare(
            'INSERT IGNORE INTO consultasmia_2 (user_id, cpf, email, tipo, evento_em, curso_id, curso, detalhe)
             VALUES (?, ?, ?, ?, ' . ($quando !== null ? '?' : 'NOW()') . ', ?, ?, ?)'
        )->execute(array_merge(
            [$userId, strlen($cpf) === 11 ? $cpf : null, $u['email'] ?: null, $tipo],
            $quando !== null ? [$quando] : [],
            [$cursoId, $curso, $detalhe ? json_encode($detalhe, JSON_UNESCAPED_UNICODE) : null]
        ));
    } catch (Throwable $e) {
        error_log('[consultasmia] evento ' . $tipo . ': ' . $e->getMessage());
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
function mse_vini_aulas_do_departamento(PDO $pdo, ?int $userId, ?string $deptoFicha): array
{
    require_once __DIR__ . '/Departamentos.php';
    if (!mse_tem_tabela($pdo, 'course_departamentos')) {
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
    // Recomendadas NÃO contam (só aparecem para a pessoa). "Obrigatório para todos" conta para todo mundo.
    $temClassif = mse_tem_coluna($pdo, 'courses', 'obrigatoriedade');
    $marc = $chaves ? implode(',', array_fill(0, count($chaves), '?')) : "''";
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
    $st = $pdo->prepare(
        "SELECT c.id, c.title, p.status, p.watched_pct, p.completed_at,
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
        "SELECT MIN(evento_em) FROM consultasmia_2 WHERE tipo = 'login' AND (detalhe IS NULL OR detalhe NOT LIKE '%historico%')"
    )->fetchColumn();
    $historico = $pdo->prepare(
        "INSERT IGNORE INTO consultasmia_2 (user_id, cpf, email, tipo, evento_em, detalhe)
         SELECT t.user_id, ?, ?, 'login', t.created_at, '{\"origem\":\"historico\"}'
         FROM auth_tokens t WHERE t.user_id = ?" . ($corte ? ' AND t.created_at < ?' : '')
    );
    // Entradas e saídas de cada vídeo (presenca_log): copiadas para o log, com a
    // mesma hora do registro original, então repetir a cópia nunca duplica.
    $temLog = mse_tem_tabela($pdo, 'presenca_log');
    $copiaVideos = $temLog ? $pdo->prepare(
        "INSERT IGNORE INTO consultasmia_2 (user_id, cpf, email, tipo, evento_em, curso_id, curso, detalhe)
         SELECT p.user_id, ?, ?, IF(p.evento = 'checkin', 'video_entrada', 'video_saida'), p.criado_em, p.course_id, c.title,
                CONCAT('{\"origem\":\"video\",\"percentual_assistido\":', IFNULL(CAST(p.watched_pct AS CHAR), 'null'), '}')
         FROM presenca_log p JOIN courses c ON c.id = p.course_id
         WHERE p.user_id = ? AND p.evento IN ('checkin', 'checkout')"
    ) : null;
    $gravadas = 0;
    foreach ($pessoas as $p) {
        $userId = !empty($p['user_id']) ? (int) $p['user_id'] : null;
        $cpf = preg_replace('/\D/', '', (string) ($p['cpf'] ?? ''));
        $cpf = strlen($cpf) === 11 ? $cpf : null;

        // O log (consultasmia_2) continua sendo completado com o histórico.
        if ($userId) {
            $args = [$cpf, $p['email'] ?? null, $userId];
            if ($corte) {
                $args[] = $corte;
            }
            $historico->execute($args);
            if ($copiaVideos) {
                $copiaVideos->execute([$cpf, $p['email'] ?? null, $userId]);
            }
        }

        // Aulas obrigatórias = as do departamento da pessoa e as obrigatórias para todos (integração fica de fora).
        $aulas = mse_vini_aulas_do_departamento($pdo, $userId, isset($p['departamento']) ? (string) $p['departamento'] : null);
        mse_vini_gravar_linhas_pessoa($pdo, (string) $p['nome'], $cpf, $p['email'] ?? null, $aulas);
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
        $aulas = mse_vini_aulas_do_departamento($pdo, $userId, null);
        mse_vini_gravar_linhas_pessoa($pdo, (string) $linha['nome'], $cpf, $linha['email'] ?: $u['email'], $aulas);
    } catch (Throwable $e) {
        error_log('[consultasmia] atualizar aulas do usuário: ' . $e->getMessage());
    }
}

/**
 * Regrava as linhas da pessoa em consultasmia: uma por aula obrigatória
 * concluída (com assunto, descrição e palavras-chave da aula). Sem nenhuma
 * concluída, fica 1 linha com a aula vazia.
 *
 * @param array $aulas as aulas obrigatórias da pessoa (mse_vini_aulas_do_departamento)
 */
function mse_vini_gravar_linhas_pessoa(PDO $pdo, string $nome, ?string $cpf, ?string $email, array $aulas): void
{
    require_once __DIR__ . '/Assuntos.php';
    $feitas = array_values(array_filter($aulas, static fn($c) => $c['status'] === 'concluido'));
    $detalhes = [];
    if ($feitas) {
        $ids = array_map(static fn($c) => (int) $c['id'], $feitas);
        $marc = implode(',', array_fill(0, count($ids), '?'));
        $temPalavras = mse_tem_coluna($pdo, 'courses', 'palavras_chave');
        $st = $pdo->prepare('SELECT c.id, c.title, c.description, a.name AS area_name, '
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
            ];
        }
    }
    $jaEmTransacao = $pdo->inTransaction();
    if (!$jaEmTransacao) {
        $pdo->beginTransaction();
    }
    try {
        if ($cpf) {
            $pdo->prepare('DELETE FROM consultasmia WHERE cpf = ?')->execute([$cpf]);
        } else {
            $pdo->prepare('DELETE FROM consultasmia WHERE cpf IS NULL AND nome = ?')->execute([$nome]);
        }
        $ins = $pdo->prepare(
            'INSERT INTO consultasmia (nome, cpf, email, aulas_obrigatorias_concluidas, aula, assunto, descricao, palavras_chave)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $linhas = $feitas ? array_map(static fn($c) => $detalhes[(int) $c['id']] ?? ['aula' => $c['curso'], 'assunto' => null, 'descricao' => null, 'palavras_chave' => null], $feitas)
                          : [['aula' => null, 'assunto' => null, 'descricao' => null, 'palavras_chave' => null]];
        foreach ($linhas as $l) {
            $ins->execute([$nome, $cpf, $email, count($feitas), $l['aula'], $l['assunto'], $l['descricao'], $l['palavras_chave']]);
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
