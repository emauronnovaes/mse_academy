<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

/**
 * Define para quais áreas um curso é obrigatório.
 *
 * Curso sem nenhuma área marcada vale pra todo mundo — é o padrão e o
 * comportamento de antes desta tela existir. Marcar áreas RESTRINGE a
 * obrigatoriedade, nunca a visibilidade: quem não é da área continua
 * vendo e podendo assistir, só não conta como pendência dele.
 *
 * GET  ?course_id=N  → lista todas as áreas e quais estão marcadas
 * POST {course_id, areas:[ids]} → substitui a seleção
 */

$pdo = mse_db();

// A tabela vem da migração 016. Enquanto ela não roda no servidor, a
// tela precisa responder em vez de estourar erro de SQL.
$temTabela = $pdo->query("SHOW TABLES LIKE 'course_areas'")->fetch() !== false;
if (!$temTabela) {
    mse_error('Este servidor ainda não tem a tabela de áreas por curso. Falta rodar a migração 016 no banco (migrations/016_cursos_por_area.sql).', 409);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $courseId = (int) ($_GET['course_id'] ?? 0);
    if ($courseId <= 0) {
        mse_error('Informe course_id.', 422);
    }

    $stmt = $pdo->prepare('SELECT id, title FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $curso = $stmt->fetch();
    if (!$curso) {
        mse_error('Aula não encontrada.', 404);
    }

    $stmt = $pdo->prepare('SELECT area_id FROM course_areas WHERE course_id = ?');
    $stmt->execute([$courseId]);
    $marcadas = array_map('intval', array_column($stmt->fetchAll(), 'area_id'));

    $areas = [];
    foreach ($pdo->query('SELECT id, slug, name FROM areas ORDER BY name ASC') as $a) {
        $areas[] = [
            'id' => (int) $a['id'],
            'slug' => $a['slug'],
            'name' => $a['name'],
            'marcada' => in_array((int) $a['id'], $marcadas, true),
        ];
    }

    mse_json([
        'course' => ['id' => (int) $curso['id'], 'title' => $curso['title']],
        'areas' => $areas,
        // Sem nenhuma marcada, o curso é obrigatório pra empresa inteira.
        'vale_para_todos' => count($marcadas) === 0,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$areas = $input['areas'] ?? [];

if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}
if (!is_array($areas)) {
    mse_error('areas precisa ser uma lista de ids.', 422);
}

$stmt = $pdo->prepare('SELECT id FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
if (!$stmt->fetch()) {
    mse_error('Aula não encontrada.', 404);
}

// Só aceita ids que existem de verdade — um id inválido viraria uma
// restrição que nunca casa com ninguém, e o curso sumiria das pendências
// de todo mundo sem explicação.
$validos = [];
if ($areas) {
    $marcadores = implode(',', array_fill(0, count($areas), '?'));
    $stmt = $pdo->prepare("SELECT id FROM areas WHERE id IN ({$marcadores})");
    $stmt->execute(array_map('intval', $areas));
    $validos = array_map('intval', array_column($stmt->fetchAll(), 'id'));
}

$pdo->beginTransaction();
try {
    $pdo->prepare('DELETE FROM course_areas WHERE course_id = ?')->execute([$courseId]);
    if ($validos) {
        $stmt = $pdo->prepare('INSERT INTO course_areas (course_id, area_id) VALUES (?, ?)');
        foreach ($validos as $areaId) {
            $stmt->execute([$courseId, $areaId]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Não consegui salvar as áreas: ' . $e->getMessage(), 500);
}

mse_json([
    'course_id' => $courseId,
    'areas' => $validos,
    'vale_para_todos' => count($validos) === 0,
    'message' => $validos
        ? 'Agora é obrigatório para ' . count($validos) . ' área(s).'
        : 'Agora é obrigatório para todas as áreas.',
]);
