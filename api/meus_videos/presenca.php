<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Treinamentos.php';
require_once __DIR__ . '/../../src/Assuntos.php';

/**
 * "Meus vídeos" > lista de presença (REH-002-F1) de um vídeo que a própria
 * pessoa enviou: os dados do treinamento que o formulário usa e quem
 * assistiu (setor, nome, e-mail, check-in e check-out). O mesmo formulário
 * que os admins baixam em Treinamentos.
 *
 * Só para o dono do vídeo (courses.enviado_por). CPF e demais dados da
 * pessoa não saem daqui: o formulário não usa.
 *
 * GET ?course_id=N
 */

mse_cors();
$usuario = mse_require_auth();
$pdo = mse_db();

$courseId = (int) ($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}
if (!mse_tem_coluna($pdo, 'courses', 'enviado_por')) {
    mse_error('Vídeo não encontrado.', 404);
}

$auditoria = mse_tem_campos_auditoria($pdo);
$temDuracao = mse_tem_coluna($pdo, 'courses', 'duration_seconds');
$st = $pdo->prepare(
    "SELECT c.id, c.title, c.description, c.type, c.area_id, c.duration_minutes, a.name AS area_name,
            " . mse_sql_tipo_efetivo($pdo) . " AS tipo,
            " . ($auditoria ? 'c.normas, c.conteudo_programatico' : 'NULL AS normas, NULL AS conteudo_programatico') . ",
            " . ($temDuracao ? 'c.duration_seconds' : 'NULL AS duration_seconds') . "
     FROM courses c LEFT JOIN areas a ON a.id = c.area_id
     WHERE c.id = ? AND c.enviado_por = ?"
);
$st->execute([$courseId, (int) $usuario['id']]);
$c = $st->fetch();
if (!$c) {
    mse_error('Vídeo não encontrado entre os que você enviou.', 404);
}

// Assuntos do vídeo (o principal primeiro) e áreas/departamentos em que é obrigatório: viram SETOR e MOTIVO.
$area = $c['area_name'];
if (mse_tem_assuntos($pdo)) {
    $ids = mse_assuntos_mapa($pdo, [$courseId])[$courseId] ?? [];
    usort($ids, static fn($x, $y) => ($x === (int) $c['area_id'] ? 0 : 1) <=> ($y === (int) $c['area_id'] ? 0 : 1));
    $nomes = [];
    foreach ($ids as $idA) {
        $sa = $pdo->prepare('SELECT name FROM areas WHERE id = ?');
        $sa->execute([$idA]);
        if (($n = $sa->fetchColumn()) !== false) {
            $nomes[] = $n;
        }
    }
    if (count($nomes) > 1) {
        $area = implode(', ', $nomes);
    }
}
$obrigatoria = [];
if (mse_tem_tabela($pdo, 'course_areas')) {
    $sa = $pdo->prepare('SELECT a.name FROM course_areas ca JOIN areas a ON a.id = ca.area_id WHERE ca.course_id = ? ORDER BY a.name');
    $sa->execute([$courseId]);
    $obrigatoria = $sa->fetchAll(PDO::FETCH_COLUMN);
}
if (mse_tem_tabela($pdo, 'course_departamentos')) {
    $sa = $pdo->prepare('SELECT departamento FROM course_departamentos WHERE course_id = ? ORDER BY departamento');
    $sa->execute([$courseId]);
    $obrigatoria = array_merge($obrigatoria, $sa->fetchAll(PDO::FETCH_COLUMN));
}

mse_json([
    'treinamento' => [
        'id' => (int) $c['id'],
        'tema' => $c['title'],
        'descricao' => $c['description'],
        'tipo' => $c['tipo'],
        'normas' => $c['normas'] !== null && $c['normas'] !== '' ? explode(',', $c['normas']) : [],
        'conteudo_programatico' => $c['conteudo_programatico'],
        'area' => $area,
        'trilha' => $c['type'] === 'onboarding',
        'duracao_seg' => $c['duration_seconds'] !== null ? (int) $c['duration_seconds'] : null,
        'duracao_min' => (int) $c['duration_minutes'],
        'areas_obrigatorias' => $obrigatoria,
    ],
    'participantes' => array_map(static fn($p) => [
        'nome' => $p['nome'],
        'email' => $p['email'],
        'departamento' => $p['departamento'],
        'checkin_em' => $p['checkin_em'],
        'checkout_em' => $p['checkout_em'],
        'ultimo_checkout_em' => $p['ultimo_checkout_em'],
    ], array_values(array_filter(
        mse_lista_presenca($pdo, $courseId),
        static fn($p) => (int) $p['user_id'] !== (int) $usuario['id'] // quem enviou não entra na própria lista
    ))),
]);
