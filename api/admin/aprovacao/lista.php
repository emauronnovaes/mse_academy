<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';
require_once __DIR__ . '/../../../src/AwsS3.php';
require_once __DIR__ . '/../../../src/Email.php';
require_once __DIR__ . '/../../../src/Departamentos.php';
require_once __DIR__ . '/../../../src/Assuntos.php';

/**
 * Tela de aprovação: vídeos esperando aprovação e, com ?id=N, tudo sobre
 * um deles (vídeo pra assistir, perguntas com a resposta certa,
 * departamentos, quem enviou). O id pode ser de um vídeo já decidido —
 * é o caso de quem abre o link do e-mail depois de outro admin.
 *
 * GET [?id=N]
 */

mse_cors();
mse_require_admin();

$pdo = mse_db();
$resposta = ['pendentes' => [], 'recusados' => [], 'video' => null, 'email_configurado' => mse_email_configurado()];
if (!mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    mse_json($resposta); // ninguém enviou nada ainda (as colunas nascem no primeiro envio)
}

$resposta['pendentes'] = array_map(static function ($l) {
    return [
        'id' => (int) $l['id'],
        'titulo' => $l['title'],
        'tipo' => $l['type'],
        'area' => $l['area_name'],
        'origem' => $l['video_source'],
        'enviado_por' => $l['autor'],
        'enviado_em' => $l['enviado_em'],
    ];
}, $pdo->query(
    "SELECT c.id, c.title, c.type, c.video_source, c.enviado_em, a.name AS area_name, u.name AS autor
     FROM courses c
     LEFT JOIN areas a ON a.id = c.area_id
     LEFT JOIN users u ON u.id = c.enviado_por
     WHERE c.aprovacao_status = 'pendente'
     ORDER BY c.enviado_em ASC, c.id ASC"
)->fetchAll());

// Histórico de recusados (os 50 mais recentes): ficam guardados, e um
// admin pode mudar de ideia e aprovar depois.
$resposta['recusados'] = array_map(static function ($l) {
    return [
        'id' => (int) $l['id'],
        'titulo' => $l['title'],
        'enviado_por' => $l['autor'],
        'recusado_por' => $l['decisor'],
        'recusado_em' => $l['decidido_em'],
        'motivo' => trim((string) $l['motivo_recusa']) !== '' ? trim((string) $l['motivo_recusa']) : null,
    ];
}, $pdo->query(
    "SELECT c.id, c.title, c.decidido_em, c.motivo_recusa, u.name AS autor, d.name AS decisor
     FROM courses c
     LEFT JOIN users u ON u.id = c.enviado_por
     LEFT JOIN users d ON d.id = c.decidido_por
     WHERE c.aprovacao_status = 'recusado'
     ORDER BY c.decidido_em DESC, c.id DESC
     LIMIT 50"
)->fetchAll());

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    mse_json($resposta);
}

$auditoria = mse_tem_coluna($pdo, 'courses', 'instrutor');
$temAviso = mse_tem_coluna($pdo, 'courses', 'aviso_presenca');
$temSorteio = mse_tem_coluna($pdo, 'courses', 'grupo_sorteio');
$stmt = $pdo->prepare(
    'SELECT c.id, c.title, c.description, c.type, c.video_source, c.youtube_id, c.video_key, c.duration_minutes,
            c.aprovacao_status, c.enviado_em, c.decidido_em, c.motivo_recusa, a.name AS area_name, a.slug AS area_slug,
            ' . ($auditoria ? 'c.tipo_treinamento, c.normas, c.instrutor, c.conteudo_programatico, c.assuntos'
                            : 'NULL AS tipo_treinamento, NULL AS normas, NULL AS instrutor, NULL AS conteudo_programatico, NULL AS assuntos') . ',
            ' . ($temAviso ? 'c.aviso_presenca' : '1 AS aviso_presenca') . ',
            ' . ($temSorteio ? 'c.grupo_sorteio, c.obrigatorio' : 'NULL AS grupo_sorteio, 1 AS obrigatorio') . ',
            u.name AS autor_nome, u.email AS autor_email, u.cargo AS autor_cargo,
            d.name AS decidido_por_nome
     FROM courses c
     LEFT JOIN areas a ON a.id = c.area_id
     LEFT JOIN users u ON u.id = c.enviado_por
     LEFT JOIN users d ON d.id = c.decidido_por
     WHERE c.id = ? AND c.aprovacao_status IS NOT NULL'
);
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    mse_error('Esse envio não existe mais (pode ter sido excluído).', 404);
}

$videoUrl = null;
$arquivoExcluido = $c['video_source'] === 's3' && trim((string) $c['video_key']) === '';
if ($c['video_source'] === 's3' && $c['video_key']) {
    try {
        $videoUrl = mse_s3_presigned_url($c['video_key'], 1800);
    } catch (Throwable $e) {
        error_log('[aprovacao] ' . $e->getMessage());
    }
}

$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$stmt = $pdo->prepare(
    'SELECT id, question_text, ' . ($temMomento ? 'momento_seg' : 'NULL AS momento_seg') . '
     FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC, id ASC'
);
$stmt->execute([$id]);
$perguntas = $stmt->fetchAll();
$opcoes = $pdo->prepare('SELECT option_text, is_correct FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC, id ASC');
foreach ($perguntas as &$p) {
    $opcoes->execute([(int) $p['id']]);
    $p = [
        'texto' => $p['question_text'],
        'momento_seg' => $p['momento_seg'] !== null ? (int) $p['momento_seg'] : null,
        'opcoes' => array_map(static function ($o) {
            return ['texto' => $o['option_text'], 'certa' => (bool) $o['is_correct']];
        }, $opcoes->fetchAll()),
    ];
}
unset($p);

$departamentos = [];
if ($pdo->query("SHOW TABLES LIKE 'course_areas'")->fetch() !== false) {
    $stmt = $pdo->prepare('SELECT a.name FROM course_areas ca JOIN areas a ON a.id = ca.area_id WHERE ca.course_id = ? ORDER BY a.name');
    $stmt->execute([$id]);
    $departamentos = array_column($stmt->fetchAll(), 'name');
}

$departamentos = array_merge($departamentos, mse_departamentos_do_curso($pdo, $id));

$resposta['video'] = [
    'id' => (int) $c['id'],
    'titulo' => $c['title'],
    'descricao' => $c['description'],
    'tipo' => $c['type'],
    'area' => $c['area_name'],
    // Todos os assuntos do vídeo (o principal primeiro).
    'assuntos_do_video' => mse_assuntos_do_curso($pdo, $id),
    'origem' => $c['video_source'],
    'youtube_id' => $c['video_source'] !== 's3' ? $c['youtube_id'] : null,
    'video_url' => $videoUrl,
    'arquivo_excluido' => $arquivoExcluido,
    'area_slug' => $c['area_slug'],
    'duracao_min' => (int) $c['duration_minutes'],
    'status' => $c['aprovacao_status'],
    'enviado_em' => $c['enviado_em'],
    'enviado_por' => ['nome' => $c['autor_nome'], 'email' => $c['autor_email'], 'cargo' => $c['autor_cargo']],
    'decidido_por' => $c['decidido_por_nome'],
    'decidido_em' => $c['decidido_em'],
    'motivo_recusa' => $c['motivo_recusa'],
    'tipo_treinamento' => $c['tipo_treinamento'],
    'normas' => $auditoria && $c['normas'] !== null && $c['normas'] !== '' ? array_values(array_filter(explode(',', (string) $c['normas']))) : [],
    'instrutor' => $c['instrutor'],
    'conteudo_programatico' => $c['conteudo_programatico'],
    'assuntos' => $c['assuntos'],
    'aviso_presenca' => (bool) $c['aviso_presenca'],
    'grupo_sorteio' => $c['grupo_sorteio'],
    'obrigatorio' => (bool) $c['obrigatorio'],
    'perguntas' => $perguntas,
    'departamentos' => $departamentos,
];
mse_json($resposta);
