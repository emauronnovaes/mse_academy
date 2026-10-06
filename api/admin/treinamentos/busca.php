<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';

/**
 * Busca de treinamentos (tela de auditoria, que substituiu "Gerenciar aulas").
 *
 * GET, todos opcionais:
 *   q                  tema, assunto, conteúdo, instrutor — ou nome, e-mail
 *                      ou CPF de quem participou
 *   modalidade         online | presencial (presencial ainda não existe na
 *                      Academy: devolve lista vazia)
 *   tipo, norma        valores de MSE_TIPOS_TREINAMENTO / MSE_NORMAS
 *   data_de, data_ate  AAAA-MM-DD — treinamentos com participação no período
 *   com_participantes  1 = inclui a lista de presença de cada um (exportar)
 *
 * Lista também as aulas arquivadas (marcadas): a auditoria pode pedir
 * evidência de um treinamento que já saiu do ar.
 */

mse_cors();
mse_require_admin();

$pdo = mse_db();

$q = trim((string) ($_GET['q'] ?? ''));
$modalidade = (string) ($_GET['modalidade'] ?? '');
$tipo = trim((string) ($_GET['tipo'] ?? ''));
$norma = trim((string) ($_GET['norma'] ?? ''));
$periodo = [
    'data_de' => (string) ($_GET['data_de'] ?? ''),
    'data_ate' => (string) ($_GET['data_ate'] ?? ''),
];
$temPeriodo = mse_data_valida($periodo['data_de']) !== null || mse_data_valida($periodo['data_ate']) !== null;
$comParticipantes = !empty($_GET['com_participantes']);

$auditoria = mse_tem_campos_auditoria($pdo);
$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$temDuracao = mse_tem_coluna($pdo, 'courses', 'duration_seconds');
$temOculto = mse_tem_coluna($pdo, 'users', 'oculto_em_relatorios');
$temCheckin = mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em');

$resposta = [
    'treinamentos' => [],
    'tipos' => MSE_TIPOS_TREINAMENTO,
    'normas' => MSE_NORMAS,
    // false = migração 020 ainda não rodou: a tela avisa que os campos de
    // auditoria e o check-in ainda não estão sendo gravados.
    'auditoria_ativa' => $auditoria && $temCheckin && $temMomento,
];

// Presencial não existe na Academy (todo treinamento aqui é um vídeo).
if ($modalidade === 'presencial') {
    mse_json($resposta);
}

$where = [];
$params = [];
if ($tipo !== '') {
    $where[] = mse_sql_tipo_efetivo($pdo) . ' = :tipo';
    $params[':tipo'] = $tipo;
}
if ($norma !== '') {
    if (!$auditoria) {
        mse_json($resposta); // sem a coluna, nenhum treinamento tem norma
    }
    $where[] = 'FIND_IN_SET(:norma, c.normas) > 0';
    $params[':norma'] = $norma;
}

$sql = "SELECT c.id, c.title, c.description, c.type, c.is_published, c.created_at, c.video_source, c.duration_minutes,
               a.name AS area_name, " . mse_sql_tipo_efetivo($pdo) . " AS tipo,
               " . ($auditoria
                    ? 'c.tipo_treinamento, c.normas, c.instrutor, c.conteudo_programatico, c.assuntos'
                    : 'NULL AS tipo_treinamento, NULL AS normas, NULL AS instrutor, NULL AS conteudo_programatico, NULL AS assuntos') . ",
               " . ($temDuracao ? 'c.duration_seconds' : 'NULL AS duration_seconds') . ",
               (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.course_id = c.id) AS total_perguntas,
               " . ($temMomento
                    ? '(SELECT COUNT(*) FROM quiz_questions qq WHERE qq.course_id = c.id AND qq.momento_seg IS NOT NULL)'
                    : '0') . " AS total_atividades
        FROM courses c
        LEFT JOIN areas a ON a.id = c.area_id"
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . "
        ORDER BY c.created_at DESC, c.id DESC";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$cursos = $stmt->fetchAll();

$areasObrigatorias = [];
if ($pdo->query("SHOW TABLES LIKE 'course_areas'")->fetch() !== false) {
    foreach ($pdo->query('SELECT ca.course_id, a.name FROM course_areas ca JOIN areas a ON a.id = ca.area_id ORDER BY a.name') as $l) {
        $areasObrigatorias[(int) $l['course_id']][] = $l['name'];
    }
}

// Contagem de participantes por treinamento (respeitando o período).
$condPresenca = $temCheckin ? "(p.status <> 'nao_iniciado' OR p.checkin_em IS NOT NULL)" : "p.status <> 'nao_iniciado'";
$contagem = function (string $pessoa) use ($pdo, $periodo, $condPresenca, $temOculto): array {
    $w = [$condPresenca];
    $pr = [];
    if ($temOculto) {
        $w[] = 'u.oculto_em_relatorios = 0';
    }
    mse_filtro_pessoa_sql($pessoa, $w, $pr);
    mse_filtro_periodo_sql($pdo, $periodo, $w, $pr);
    $st = $pdo->prepare(
        "SELECT p.course_id, COUNT(*) AS participantes, SUM(p.status = 'concluido') AS concluidos
         FROM user_course_progress p JOIN users u ON u.id = p.user_id
         WHERE " . implode(' AND ', $w) . ' GROUP BY p.course_id'
    );
    foreach ($pr as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->execute();
    $porCurso = [];
    foreach ($st->fetchAll() as $l) {
        $porCurso[(int) $l['course_id']] = ['participantes' => (int) $l['participantes'], 'concluidos' => (int) $l['concluidos']];
    }
    return $porCurso;
};
$contagens = $contagem('');
$porPessoa = $q !== '' ? $contagem($q) : [];

// Texto da busca bate com o treinamento? Sem acento e sem maiúscula, pra
// "seguranca" achar "Segurança".
$qNorm = $q !== '' ? mse_normalize_text($q) : '';
$bateTexto = function (array $c) use ($qNorm): bool {
    $campos = [$c['title'], $c['description'], $c['tipo'], $c['normas'], $c['instrutor'],
               $c['conteudo_programatico'], $c['assuntos'], $c['area_name']];
    foreach ($campos as $campo) {
        if ($campo !== null && $campo !== '' && mse_str_contains(mse_normalize_text((string) $campo), $qNorm)) {
            return true;
        }
    }
    return false;
};

foreach ($cursos as $c) {
    $id = (int) $c['id'];
    $porTexto = $qNorm === '' || $bateTexto($c);
    $porParticipante = $q !== '' && !$porTexto && isset($porPessoa[$id]);
    if (!$porTexto && !$porParticipante) {
        continue;
    }
    $cont = $contagens[$id] ?? ['participantes' => 0, 'concluidos' => 0];
    if ($temPeriodo && $cont['participantes'] === 0) {
        continue; // ninguém participou no período
    }

    $item = [
        'id' => $id,
        'data' => $c['created_at'],
        'tema' => $c['title'],
        'descricao' => $c['description'],
        'modalidade' => 'Online',
        'tipo' => $c['tipo'],
        'tipo_preenchido' => $c['tipo_treinamento'] !== null,
        'normas' => $c['normas'] !== null && $c['normas'] !== '' ? explode(',', $c['normas']) : [],
        'instrutor' => $c['instrutor'],
        'conteudo_programatico' => $c['conteudo_programatico'],
        'assuntos' => $c['assuntos'],
        'area' => $c['area_name'],
        'trilha' => $c['type'] === 'onboarding',
        'type' => $c['type'],
        'arquivado' => (int) $c['is_published'] === 0,
        'video_source' => $c['video_source'],
        'duracao_seg' => $c['duration_seconds'] !== null ? (int) $c['duration_seconds'] : null,
        'duracao_min' => (int) $c['duration_minutes'],
        // Áreas pra quais a aula é obrigatória (vazio = todos): vira o
        // "Motivo" da lista de presença.
        'areas_obrigatorias' => $areasObrigatorias[$id] ?? [],
        'total_perguntas' => (int) $c['total_perguntas'],
        'total_atividades' => (int) $c['total_atividades'],
        'participantes' => $cont['participantes'],
        'concluidos' => $cont['concluidos'],
        // Achado pelo nome/CPF de alguém: a lista de presença exportada
        // mostra só essa(s) pessoa(s), não a turma inteira.
        'encontrado_por_participante' => $porParticipante,
    ];
    if ($comParticipantes) {
        $item['lista_presenca'] = mse_lista_presenca($pdo, $id, $periodo + ['q' => $porParticipante ? $q : '']);
    }
    $resposta['treinamentos'][] = $item;
}

mse_json($resposta);
