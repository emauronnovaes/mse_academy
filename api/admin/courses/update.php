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

/**
 * Edita os textos de uma aula (título e descrição). Não mexe em vídeo,
 * área nem quiz — trocar o nome de uma aula não deveria exigir recriar
 * nada disso, nem arriscar perder o histórico de quem já assistiu.
 *
 * Só os campos enviados são alterados: mandar apenas "title" deixa a
 * descrição como está.
 */

$input = mse_input();

$courseId = (int) ($input['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$pdo = mse_db();

$stmt = $pdo->prepare('SELECT id, title, description FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    mse_error('Aula não encontrada.', 404);
}

$campos = [];
$valores = [];

if (array_key_exists('title', $input)) {
    $title = trim((string) $input['title']);
    if ($title === '') {
        mse_error('O título não pode ficar vazio.', 422);
    }
    if (mb_strlen($title) > 200) {
        mse_error('Título longo demais (máximo 200 caracteres).', 422);
    }
    $campos[] = 'title = ?';
    $valores[] = $title;
}

if (array_key_exists('description', $input)) {
    $campos[] = 'description = ?';
    $valores[] = trim((string) $input['description']);
}

if (!$campos) {
    mse_error('Nada para alterar — envie title e/ou description.', 422);
}

$valores[] = $courseId;
$stmt = $pdo->prepare('UPDATE courses SET ' . implode(', ', $campos) . ' WHERE id = ?');
$stmt->execute($valores);

$stmt = $pdo->prepare('SELECT id, title, description FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$atualizado = $stmt->fetch();

mse_json([
    'course' => [
        'id' => (int) $atualizado['id'],
        'title' => $atualizado['title'],
        'description' => $atualizado['description'],
    ],
    'titulo_anterior' => $course['title'],
    'message' => 'Aula atualizada.',
]);
