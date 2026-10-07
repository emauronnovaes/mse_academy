<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';

/**
 * Dados de treinamento pra auditoria (ISO 9001, 14001 e 45001) e a lista
 * de presença de cada um. Usado pelo cadastro e edição de aula e pela
 * tela "Busca de treinamentos".
 *
 * Cada vídeo da Academy é um treinamento online. O que a auditoria pede
 * dele (tipo, normas, instrutor, conteúdo programático, assuntos) não vem
 * de API nenhuma: o admin preenche uma vez por vídeo. Já a presença de
 * cada pessoa é toda automática — check-in ao abrir, check-out ao
 * concluir, confirmações de "Estou aqui" e o resultado das atividades.
 */

const MSE_TIPOS_TREINAMENTO = ['Treinamento interno', 'DDS', 'Capacitação externa', 'Integração', 'Outro'];
const MSE_NORMAS = ['ISO 9001', 'ISO 14001', 'ISO 45001'];

/** true quando a migração 020 já rodou (colunas de auditoria em courses). */
function mse_tem_campos_auditoria(PDO $pdo): bool
{
    return mse_tem_coluna($pdo, 'courses', 'tipo_treinamento');
}

/**
 * Lê e valida os campos de auditoria que vierem no corpo. Só devolve as
 * chaves enviadas — o que não veio não é tocado na edição.
 *
 * @return array<string, string|null> coluna => valor (null = vazio)
 */
function mse_ler_campos_auditoria(PDO $pdo, array $input): array
{
    $chaves = ['tipo_treinamento', 'normas', 'instrutor', 'conteudo_programatico', 'assuntos'];
    $enviados = array_intersect($chaves, array_keys($input));
    if (!$enviados) {
        return [];
    }
    if (!mse_tem_campos_auditoria($pdo)) {
        // Campo em branco não precisa da migração; campo preenchido precisa.
        foreach ($enviados as $k) {
            $v = $input[$k];
            if ((is_array($v) && $v) || (!is_array($v) && trim((string) $v) !== '')) {
                mse_error('Os dados de auditoria ainda não podem ser gravados: falta rodar a migração 020 no banco.', 409);
            }
        }
        return [];
    }

    $saida = [];
    $texto = function (string $k, int $max, string $nome) use ($input): ?string {
        $v = trim((string) ($input[$k] ?? ''));
        if (mb_strlen($v) > $max) {
            mse_error($nome . ' passou de ' . $max . ' caracteres.', 422);
        }
        return $v === '' ? null : $v;
    };

    if (in_array('tipo_treinamento', $enviados, true)) {
        $tipo = $texto('tipo_treinamento', 60, 'O tipo');
        if ($tipo !== null && !in_array($tipo, MSE_TIPOS_TREINAMENTO, true)) {
            mse_error('Tipo de treinamento inválido. Use: ' . implode(', ', MSE_TIPOS_TREINAMENTO) . '.', 422);
        }
        $saida['tipo_treinamento'] = $tipo;
    }
    if (in_array('normas', $enviados, true)) {
        $lista = $input['normas'];
        if (!is_array($lista)) {
            $lista = explode(',', (string) $lista);
        }
        $lista = array_map('trim', $lista);
        $invalidas = array_diff(array_filter($lista, 'strlen'), MSE_NORMAS);
        if ($invalidas) {
            mse_error('Norma inválida: ' . implode(', ', $invalidas) . '. Use: ' . implode(', ', MSE_NORMAS) . '.', 422);
        }
        // Sempre na mesma ordem e sem espaço depois da vírgula: é o formato
        // que o FIND_IN_SET da busca entende.
        $normas = array_values(array_filter(MSE_NORMAS, fn($n) => in_array($n, $lista, true)));
        $saida['normas'] = $normas ? implode(',', $normas) : null;
    }
    if (in_array('instrutor', $enviados, true)) {
        $saida['instrutor'] = $texto('instrutor', 150, 'O instrutor');
    }
    if (in_array('conteudo_programatico', $enviados, true)) {
        $saida['conteudo_programatico'] = $texto('conteudo_programatico', 5000, 'O conteúdo programático');
    }
    if (in_array('assuntos', $enviados, true)) {
        $saida['assuntos'] = $texto('assuntos', 500, 'Os assuntos');
    }
    return $saida;
}

/**
 * Tipo mostrado quando ninguém preencheu: aula da trilha é integração,
 * aula do catálogo é treinamento interno. Mesma expressão do SQL da busca.
 */
function mse_sql_tipo_efetivo(PDO $pdo): string
{
    $padrao = "CASE c.type WHEN 'onboarding' THEN 'Integração' ELSE 'Treinamento interno' END";
    return mse_tem_campos_auditoria($pdo) ? "COALESCE(c.tipo_treinamento, {$padrao})" : $padrao;
}

/**
 * Lista de presença de um treinamento: todo mundo que abriu o vídeo.
 *
 * @param array{q?:string, data_de?:string, data_ate?:string} $filtros
 *   q filtra por nome, e-mail ou CPF; as datas, pelo período em que a
 *   pessoa participou (check-in, conclusão ou última atividade).
 */
function mse_lista_presenca(PDO $pdo, int $courseId, array $filtros = []): array
{
    $temCheckin = mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em');
    $temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
    $temDuracao = mse_tem_coluna($pdo, 'courses', 'duration_seconds');

    $where = ['p.course_id = :cid'];
    $params = [':cid' => $courseId];

    // Quem só tem a linha "nao_iniciado" sem check-in nunca abriu de fato
    // (é resto de versões antigas). Com check-in, abriu — e conta.
    $where[] = $temCheckin ? "(p.status <> 'nao_iniciado' OR p.checkin_em IS NOT NULL)" : "p.status <> 'nao_iniciado'";

    if (mse_tem_coluna($pdo, 'users', 'oculto_em_relatorios')) {
        $where[] = 'u.oculto_em_relatorios = 0';
    }
    mse_filtro_pessoa_sql($filtros['q'] ?? '', $where, $params);
    mse_filtro_periodo_sql($pdo, $filtros, $where, $params);

    $sql = "SELECT u.id AS user_id, u.name, u.email, u.cpf, u.cargo, a.name AS area_name,
                   p.status, p.watched_pct, p.completed_at, p.updated_at,
                   " . ($temCheckin ? 'p.checkin_em, p.confirmacoes_presenca, p.ultima_confirmacao_em' : 'NULL AS checkin_em, 0 AS confirmacoes_presenca, NULL AS ultima_confirmacao_em') . ",
                   " . ($temDuracao ? 'c.duration_seconds' : 'NULL AS duration_seconds') . ",
                   (SELECT COUNT(DISTINCT t.question_id) FROM quiz_attempts t
                      JOIN quiz_questions qq ON qq.id = t.question_id
                     WHERE qq.course_id = p.course_id AND t.user_id = p.user_id AND t.is_correct = 1) AS acertos,
                   (SELECT COUNT(*) FROM quiz_attempts t
                      JOIN quiz_questions qq ON qq.id = t.question_id
                     WHERE qq.course_id = p.course_id AND t.user_id = p.user_id) AS tentativas,
                   (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.course_id = p.course_id) AS total_perguntas,
                   " . mse_sql_resumo_log($pdo) . "
            FROM user_course_progress p
            JOIN users u ON u.id = p.user_id
            JOIN courses c ON c.id = p.course_id
            LEFT JOIN areas a ON a.id = u.area_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY u.name ASC";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();

    return array_map(function ($r) {
        $pct = (float) $r['watched_pct'];
        $dur = $r['duration_seconds'] !== null ? (int) $r['duration_seconds'] : 0;
        return [
            'user_id' => (int) $r['user_id'],
            'nome' => $r['name'],
            'email' => $r['email'],
            'cpf' => $r['cpf'],
            'cargo' => $r['cargo'],
            'departamento' => $r['area_name'],
            'status' => $r['status'],
            'watched_pct' => $pct,
            // Check-in: o gravado no progresso ou, se não houver, o primeiro do log.
            'checkin_em' => $r['checkin_em'] ?? $r['primeiro_checkin_log'],
            'checkout_em' => $r['completed_at'],
            // Do log (migração 021): quantas vezes entrou e a última saída.
            'sessoes' => (int) $r['sessoes'],
            'ultimo_checkout_em' => $r['ultimo_checkout_log'],
            'ultima_atividade_em' => $r['updated_at'],
            // Estimativa: o sistema guarda até onde a pessoa chegou, não um
            // cronômetro. Com a trava de avanço, chegar a X% exige assistir.
            'tempo_assistido_seg' => $dur > 0 ? (int) round($dur * $pct / 100) : null,
            'confirmacoes_presenca' => (int) $r['confirmacoes_presenca'],
            'ultima_confirmacao_em' => $r['ultima_confirmacao_em'],
            'acertos' => (int) $r['acertos'],
            'tentativas' => (int) $r['tentativas'],
            'total_perguntas' => (int) $r['total_perguntas'],
        ];
    }, $stmt->fetchAll());
}

/** Busca por pessoa: nome, e-mail ou CPF (com ou sem pontuação). */
function mse_filtro_pessoa_sql(string $q, array &$where, array &$params, string $alias = 'u'): void
{
    $q = trim($q);
    if ($q === '') {
        return;
    }
    $partes = ["{$alias}.name LIKE :pq_nome", "{$alias}.email LIKE :pq_email"];
    $params[':pq_nome'] = '%' . $q . '%';
    $params[':pq_email'] = '%' . $q . '%';
    $digitos = preg_replace('/\D+/', '', $q);
    if ($digitos !== '' && strlen($digitos) >= 3) {
        $partes[] = "{$alias}.cpf LIKE :pq_cpf";
        $params[':pq_cpf'] = '%' . $digitos . '%';
    }
    $where[] = '(' . implode(' OR ', $partes) . ')';
}

/** Período de participação: check-in, conclusão ou última atividade no intervalo. */
function mse_filtro_periodo_sql(PDO $pdo, array $filtros, array &$where, array &$params, string $alias = 'p'): void
{
    $de = mse_data_valida($filtros['data_de'] ?? '');
    $ate = mse_data_valida($filtros['data_ate'] ?? '');
    if ($de === null && $ate === null) {
        return;
    }
    $colunas = ["{$alias}.completed_at", "{$alias}.updated_at"];
    if (mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em')) {
        array_unshift($colunas, "{$alias}.checkin_em");
    }
    $partes = [];
    foreach ($colunas as $i => $col) {
        $cond = [];
        if ($de !== null) {
            $cond[] = "{$col} >= :per_de{$i}";
            $params[":per_de{$i}"] = $de . ' 00:00:00';
        }
        if ($ate !== null) {
            $cond[] = "{$col} <= :per_ate{$i}";
            $params[":per_ate{$i}"] = $ate . ' 23:59:59';
        }
        $partes[] = '(' . implode(' AND ', $cond) . ')';
    }
    $where[] = '(' . implode(' OR ', $partes) . ')';
}

function mse_data_valida(string $data): ?string
{
    $data = trim($data);
    if ($data === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return null;
    }
    [$a, $m, $d] = array_map('intval', explode('-', $data));
    return checkdate($m, $d, $a) ? $data : null;
}

/**
 * Filtros de treinamento (tipo, norma) e de período, aplicados sobre
 * courses c / user_course_progress p. Usado pelo relatório por pessoa.
 */
function mse_filtros_treinamento_sql(PDO $pdo, array $filtros, array &$where, array &$params): void
{
    $tipo = trim((string) ($filtros['tipo'] ?? ''));
    $norma = trim((string) ($filtros['norma'] ?? ''));
    if ($tipo !== '') {
        $where[] = mse_sql_tipo_efetivo($pdo) . ' = :ft_tipo';
        $params[':ft_tipo'] = $tipo;
    }
    if ($norma !== '') {
        if (mse_tem_campos_auditoria($pdo)) {
            $where[] = 'FIND_IN_SET(:ft_norma, c.normas) > 0';
            $params[':ft_norma'] = $norma;
        } else {
            $where[] = '1 = 0'; // migração 020 pendente: nenhum treinamento tem norma
        }
    }
    mse_filtro_periodo_sql($pdo, $filtros, $where, $params);
}

/** Condição de "participou": abriu o vídeo (com check-in) ou começou a assistir. */
function mse_sql_participou(PDO $pdo): string
{
    return mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em')
        ? "(p.status <> 'nao_iniciado' OR p.checkin_em IS NOT NULL)"
        : "p.status <> 'nao_iniciado'";
}

/**
 * Relatório por pessoa: todo colaborador ativo, com quantos treinamentos
 * fez dentro dos filtros. Quem tem zero aparece também — pra auditoria,
 * saber quem NÃO fez é tão importante quanto quem fez.
 */
function mse_lista_pessoas(PDO $pdo, array $filtros): array
{
    $cond = [mse_sql_participou($pdo)];
    $params = [];
    mse_filtros_treinamento_sql($pdo, $filtros, $cond, $params);

    $where = ['u.active = 1'];
    if (mse_tem_coluna($pdo, 'users', 'oculto_em_relatorios')) {
        $where[] = 'u.oculto_em_relatorios = 0';
    }
    mse_filtro_pessoa_sql((string) ($filtros['q'] ?? ''), $where, $params);

    // O filtro de treinamento fica no JOIN, não no WHERE: assim quem não
    // fez nenhum treinamento continua na lista, com zero.
    $sql = "SELECT u.id, u.name, u.email, u.cpf, u.cargo, a.name AS area_name, u.last_access_date,
                   u.distinct_access_count,
                   (SELECT COUNT(*) FROM user_course_progress pi JOIN courses ci ON ci.id = pi.course_id
                     WHERE pi.user_id = u.id AND ci.type = 'onboarding' AND pi.status = 'concluido') AS integracao_concluidos,
                   COUNT(c.id) AS treinamentos,
                   COALESCE(SUM(c.id IS NOT NULL AND p.status = 'concluido'), 0) AS concluidos
            FROM users u
            LEFT JOIN areas a ON a.id = u.area_id
            LEFT JOIN user_course_progress p ON p.user_id = u.id
            LEFT JOIN courses c ON c.id = p.course_id AND " . implode(' AND ', $cond) . "
            WHERE " . implode(' AND ', $where) . "
            GROUP BY u.id, u.name, u.email, u.cpf, u.cargo, a.name, u.last_access_date, u.distinct_access_count
            ORDER BY u.name ASC";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();

    return array_map(function ($r) {
        return [
            'user_id' => (int) $r['id'],
            'nome' => $r['name'],
            'email' => $r['email'],
            'cpf' => $r['cpf'],
            'cargo' => $r['cargo'],
            'departamento' => $r['area_name'],
            'ultimo_acesso' => $r['last_access_date'],
            // O que a antiga tela "Acessos" mostrava: dias em que entrou e
            // módulos da integração concluídos.
            'acessos' => (int) $r['distinct_access_count'],
            'integracao_concluidos' => (int) $r['integracao_concluidos'],
            'treinamentos' => (int) $r['treinamentos'],
            'concluidos' => (int) $r['concluidos'],
        ];
    }, $stmt->fetchAll());
}

/** Ficha de uma pessoa: cada treinamento de que participou, com a evidência. */
function mse_treinamentos_da_pessoa(PDO $pdo, int $userId, array $filtros): array
{
    $temCheckin = mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em');
    $temDuracao = mse_tem_coluna($pdo, 'courses', 'duration_seconds');
    $auditoria = mse_tem_campos_auditoria($pdo);

    $where = ['p.user_id = :uid', mse_sql_participou($pdo)];
    $params = [':uid' => $userId];
    mse_filtros_treinamento_sql($pdo, $filtros, $where, $params);

    $sql = "SELECT c.id, c.title, c.description, c.created_at, c.is_published, " . mse_sql_tipo_efetivo($pdo) . " AS tipo,
                   " . ($auditoria ? 'c.normas, c.instrutor, c.conteudo_programatico, c.assuntos'
                                   : 'NULL AS normas, NULL AS instrutor, NULL AS conteudo_programatico, NULL AS assuntos') . ",
                   " . ($temDuracao ? 'c.duration_seconds' : 'NULL AS duration_seconds') . ",
                   p.status, p.watched_pct, p.completed_at, p.updated_at,
                   " . ($temCheckin ? 'p.checkin_em, p.confirmacoes_presenca, p.ultima_confirmacao_em'
                                    : 'NULL AS checkin_em, 0 AS confirmacoes_presenca, NULL AS ultima_confirmacao_em') . ",
                   (SELECT COUNT(DISTINCT t.question_id) FROM quiz_attempts t JOIN quiz_questions qq ON qq.id = t.question_id
                     WHERE qq.course_id = c.id AND t.user_id = p.user_id AND t.is_correct = 1) AS acertos,
                   (SELECT COUNT(*) FROM quiz_attempts t JOIN quiz_questions qq ON qq.id = t.question_id
                     WHERE qq.course_id = c.id AND t.user_id = p.user_id) AS tentativas,
                   (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.course_id = c.id) AS total_perguntas,
                   " . mse_sql_resumo_log($pdo) . "
            FROM user_course_progress p
            JOIN courses c ON c.id = p.course_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY COALESCE(" . ($temCheckin ? 'p.checkin_em, ' : '') . "p.completed_at, p.updated_at) DESC";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();

    return array_map(function ($r) {
        $pct = (float) $r['watched_pct'];
        $dur = $r['duration_seconds'] !== null ? (int) $r['duration_seconds'] : 0;
        return [
            'id' => (int) $r['id'],
            'tema' => $r['title'],
            'descricao' => $r['description'],
            'data' => $r['created_at'],
            'arquivado' => (int) $r['is_published'] === 0,
            'tipo' => $r['tipo'],
            'normas' => $r['normas'] !== null && $r['normas'] !== '' ? explode(',', $r['normas']) : [],
            'instrutor' => $r['instrutor'],
            'conteudo_programatico' => $r['conteudo_programatico'],
            'assuntos' => $r['assuntos'],
            'status' => $r['status'],
            'watched_pct' => $pct,
            'tempo_assistido_seg' => $dur > 0 ? (int) round($dur * $pct / 100) : null,
            // Check-in: o gravado no progresso ou, se não houver, o primeiro do log.
            'checkin_em' => $r['checkin_em'] ?? $r['primeiro_checkin_log'],
            'checkout_em' => $r['completed_at'],
            // Do log (migração 021): quantas vezes entrou e a última saída.
            'sessoes' => (int) $r['sessoes'],
            'ultimo_checkout_em' => $r['ultimo_checkout_log'],
            'confirmacoes_presenca' => (int) $r['confirmacoes_presenca'],
            'ultima_confirmacao_em' => $r['ultima_confirmacao_em'],
            'acertos' => (int) $r['acertos'],
            'tentativas' => (int) $r['tentativas'],
            'total_perguntas' => (int) $r['total_perguntas'],
        ];
    }, $stmt->fetchAll());
}


/**
 * Colunas de resumo do log de presença pra um SELECT com p (progresso):
 * sessões (check-ins), primeiro check-in e último check-out.
 */
function mse_sql_resumo_log(PDO $pdo): string
{
    if (!mse_tem_tabela($pdo, 'presenca_log')) {
        return '0 AS sessoes, NULL AS primeiro_checkin_log, NULL AS ultimo_checkout_log';
    }
    $base = 'FROM presenca_log l WHERE l.user_id = p.user_id AND l.course_id = p.course_id';
    return "(SELECT COUNT(*) {$base} AND l.evento = 'checkin') AS sessoes,
            (SELECT MIN(l.criado_em) {$base} AND l.evento = 'checkin') AS primeiro_checkin_log,
            (SELECT MAX(l.criado_em) {$base} AND l.evento = 'checkout') AS ultimo_checkout_log";
}

/**
 * Log de presença de uma pessoa: cada check-in, check-out e "Estou aqui",
 * na ordem em que aconteceram. Com course_id, só daquele treinamento.
 */
function mse_log_presenca(PDO $pdo, int $userId, ?int $courseId, array $filtros = []): array
{
    if (!mse_tem_tabela($pdo, 'presenca_log')) {
        return [];
    }
    $where = ['l.user_id = :uid'];
    $params = [':uid' => $userId];
    if ($courseId) {
        $where[] = 'l.course_id = :cid';
        $params[':cid'] = $courseId;
    }
    $de = mse_data_valida((string) ($filtros['data_de'] ?? ''));
    $ate = mse_data_valida((string) ($filtros['data_ate'] ?? ''));
    if ($de !== null) {
        $where[] = 'l.criado_em >= :de';
        $params[':de'] = $de . ' 00:00:00';
    }
    if ($ate !== null) {
        $where[] = 'l.criado_em <= :ate';
        $params[':ate'] = $ate . ' 23:59:59';
    }
    $stmt = $pdo->prepare(
        'SELECT l.course_id, c.title, l.evento, l.watched_pct, l.criado_em, l.user_agent
         FROM presenca_log l JOIN courses c ON c.id = l.course_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY l.criado_em ASC, l.id ASC'
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    return array_map(function ($r) {
        return [
            'course_id' => (int) $r['course_id'],
            'tema' => $r['title'],
            'evento' => $r['evento'],
            'watched_pct' => $r['watched_pct'] !== null ? (float) $r['watched_pct'] : null,
            'em' => $r['criado_em'],
            'navegador' => $r['user_agent'],
        ];
    }, $stmt->fetchAll());
}


/**
 * "aviso_presenca" do corpo (liga/desliga o "Você ainda está aí?" do
 * vídeo). Devolve [] quando não veio. Desligar exige a migração 022;
 * ligar sem ela não muda nada (já é o comportamento de todo vídeo).
 *
 * @return array<string, int>
 */
function mse_ler_aviso_presenca(PDO $pdo, array $input): array
{
    if (!array_key_exists('aviso_presenca', $input)) {
        return [];
    }
    $ligado = !empty($input['aviso_presenca']);
    if (!mse_tem_coluna($pdo, 'courses', 'aviso_presenca')) {
        if (!$ligado) {
            mse_error('Pra desligar o aviso "Você ainda está aí?" falta rodar a migração 022 no banco.', 409);
        }
        return [];
    }
    return ['aviso_presenca' => $ligado ? 1 : 0];
}
