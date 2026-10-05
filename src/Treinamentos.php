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
                   (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.course_id = p.course_id) AS total_perguntas
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
            'checkin_em' => $r['checkin_em'],
            'checkout_em' => $r['completed_at'],
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
