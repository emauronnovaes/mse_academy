<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';
require_once __DIR__ . '/../../src/Assuntos.php';
require_once __DIR__ . '/../../src/Departamentos.php';

/**
 * "Meus vídeos": os vídeos que a própria pessoa enviou (courses.enviado_por =
 * ela), em qualquer situação, com o que ela precisa para editar.
 *
 * Só devolve os dela. Vídeo de outra pessoa, ou cadastrado por admin, nunca
 * aparece aqui.
 *
 * GET
 */

mse_cors();
$usuario = mse_require_auth();
$pdo = mse_db();

if (!mse_tem_coluna($pdo, 'courses', 'enviado_por')) {
    mse_json(['videos' => [], 'total' => 0]);
}

$audit = mse_tem_coluna($pdo, 'courses', 'instrutor');
$temAviso = mse_tem_coluna($pdo, 'courses', 'aviso_presenca');
$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$stmt = $pdo->prepare(
    'SELECT c.id, c.title, c.description, c.type, c.video_source, c.video_key, c.duration_minutes, c.is_published,
            c.aprovacao_status, c.enviado_em, c.decidido_em, c.motivo_recusa, a.slug AS area_slug, a.name AS area_name,
            ' . ($audit ? 'c.tipo_treinamento, c.normas, c.instrutor, c.conteudo_programatico, c.assuntos'
                        : 'NULL AS tipo_treinamento, NULL AS normas, NULL AS instrutor, NULL AS conteudo_programatico, NULL AS assuntos') . ',
            ' . ($temAviso ? 'c.aviso_presenca' : '1 AS aviso_presenca') . ',
            (SELECT COUNT(*) FROM quiz_questions q WHERE q.course_id = c.id) AS total_perguntas
     FROM courses c
     LEFT JOIN areas a ON a.id = c.area_id
     WHERE c.enviado_por IS NOT NULL AND c.enviado_por = ?
     ORDER BY c.enviado_em DESC, c.id DESC
     LIMIT 200'
);
$stmt->execute([(int) $usuario['id']]);

$videos = [];
foreach ($stmt->fetchAll() as $c) {
    $id = (int) $c['id'];
    $videos[] = [
        'id' => $id,
        'titulo' => $c['title'],
        'descricao' => $c['description'],
        'tipo' => $c['type'],
        'status' => $c['aprovacao_status'] ?: ((int) $c['is_published'] === 1 ? 'aprovado' : 'pendente'),
        'motivo_recusa' => $c['motivo_recusa'],
        'enviado_em' => $c['enviado_em'],
        'decidido_em' => $c['decidido_em'],
        'origem' => $c['video_source'],
        'arquivo_excluido' => $c['video_source'] === 's3' && trim((string) $c['video_key']) === '',
        'duracao_min' => (int) $c['duration_minutes'],
        'area' => $c['area_name'],
        'area_slug' => $c['area_slug'],
        'assuntos_do_video' => mse_assuntos_do_curso($pdo, $id),
        'departamentos' => mse_departamentos_do_curso($pdo, $id),
        'aviso_presenca' => (bool) $c['aviso_presenca'],
        'tipo_treinamento' => $c['tipo_treinamento'],
        'normas' => $c['normas'] !== null && $c['normas'] !== '' ? array_values(array_filter(explode(',', (string) $c['normas']))) : [],
        'instrutor' => $c['instrutor'],
        'conteudo_programatico' => $c['conteudo_programatico'],
        'assuntos' => $c['assuntos'],
        'total_perguntas' => (int) $c['total_perguntas'],
    ];
}

mse_json(['videos' => $videos, 'total' => count($videos)]);
