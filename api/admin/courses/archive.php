<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();

$courseId = (int) ($input['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

// Sem "published" explícito, alterna o estado atual — deixa o front
// mandar só o id quando o botão for de liga/desliga.
$published = isset($input['published']) ? (int) (bool) $input['published'] : null;

$pdo = mse_db();

$stmt = $pdo->prepare('SELECT id, title, is_published FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    mse_error('Aula não encontrada.', 404);
}

if ($published === null) {
    $published = ((int) $course['is_published'] === 1) ? 0 : 1;
}

$stmt = $pdo->prepare('UPDATE courses SET is_published = ? WHERE id = ?');
$stmt->execute([$published, $courseId]);

mse_json([
    'course' => [
        'id' => (int) $course['id'],
        'title' => $course['title'],
        'is_published' => $published,
    ],
    'message' => $published === 1
        ? 'Aula republicada — voltou a aparecer para os colaboradores.'
        : 'Aula arquivada — sumiu da Academy, mas o histórico de quem assistiu foi preservado.',
]);
