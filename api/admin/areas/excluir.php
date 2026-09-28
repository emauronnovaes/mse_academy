<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

/**
 * Exclui um departamento.
 *
 * Em dois passos: a primeira chamada só diz o que vai acontecer, a segunda
 * (com "confirmar": true) faz. É irreversível e mexe em coisa que não está
 * à vista — não dá pra um clique só.
 *
 * Departamento com vídeo não é excluído. O vídeo continuaria no banco sem
 * nenhuma tela onde aparecer: a seção "Cursos" lista por departamento, e
 * sem departamento ele não entra em lista nenhuma. Some da vista sem ter
 * sido apagado, que é o pior dos dois mundos. O banco já recusa por conta
 * própria (courses.area_id é ON DELETE RESTRICT); a checagem aqui existe
 * pra virar uma frase explicando o motivo em vez de um erro de SQL cru.
 *
 * O que a exclusão leva junto, e por isso aparece no resumo:
 *  - quem era desse departamento fica sem departamento (users.area_id vira
 *    NULL, por ON DELETE SET NULL). A conta continua igual, mas muda quais
 *    cursos contam como obrigatórios pra essa pessoa.
 *  - as regras de cargo que apontavam pra ele são apagadas (cargo_areas,
 *    ON DELETE CASCADE), então cargo que caía aqui deixa de cair.
 *  - as marcações de "curso também obrigatório nesta área" somem
 *    (course_areas, ON DELETE CASCADE).
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$pdo = mse_db();
$input = mse_input();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    mse_error('Informe o departamento.', 422);
}

$stmt = $pdo->prepare('SELECT id, slug, name FROM areas WHERE id = ?');
$stmt->execute([$id]);
$area = $stmt->fetch();
if (!$area) {
    mse_error('Departamento não encontrado.', 404);
}

// Conta TODOS os vídeos, inclusive os arquivados: arquivado continua
// existindo no banco e pode ser desarquivado depois.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE area_id = ?');
$stmt->execute([$id]);
$totalCursos = (int) $stmt->fetchColumn();

if ($totalCursos > 0) {
    mse_error(
        'O departamento "' . $area['name'] . '" tem ' . $totalCursos . ' '
        . ($totalCursos === 1 ? 'vídeo' : 'vídeos')
        . ' (contando os arquivados). Excluir deixaria '
        . ($totalCursos === 1 ? 'esse vídeo' : 'esses vídeos')
        . ' sem nenhuma tela onde aparecer. Mova '
        . ($totalCursos === 1 ? 'o vídeo' : 'os vídeos')
        . ' para outro departamento ou exclua em "Gerenciar aulas" primeiro.',
        409
    );
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE area_id = ?');
$stmt->execute([$id]);
$totalPessoas = (int) $stmt->fetchColumn();

$totalRegrasCargo = 0;
if ($pdo->query("SHOW TABLES LIKE 'cargo_areas'")->fetch() !== false) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cargo_areas WHERE area_id = ?');
    $stmt->execute([$id]);
    $totalRegrasCargo = (int) $stmt->fetchColumn();
}

$totalVinculos = 0;
if ($pdo->query("SHOW TABLES LIKE 'course_areas'")->fetch() !== false) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM course_areas WHERE area_id = ?');
    $stmt->execute([$id]);
    $totalVinculos = (int) $stmt->fetchColumn();
}

// ------------------------------------------------------------
// Passo 1: só conta o que vai acontecer
// ------------------------------------------------------------
if (empty($input['confirmar'])) {
    $consequencias = [];
    if ($totalPessoas > 0) {
        $consequencias[] = $totalPessoas . ' '
            . ($totalPessoas === 1 ? 'pessoa fica' : 'pessoas ficam')
            . ' sem departamento. '
            . ($totalPessoas === 1 ? 'A conta dela continua' : 'As contas delas continuam')
            . ' normal, mas muda quais cursos contam como obrigatórios pra '
            . ($totalPessoas === 1 ? 'ela' : 'elas') . '.';
    }
    if ($totalRegrasCargo > 0) {
        $consequencias[] = $totalRegrasCargo . ' '
            . ($totalRegrasCargo === 1 ? 'regra de cargo é apagada' : 'regras de cargo são apagadas')
            . ': cargo que caía neste departamento deixa de cair.';
    }
    if ($totalVinculos > 0) {
        $consequencias[] = $totalVinculos . ' '
            . ($totalVinculos === 1 ? 'marcação' : 'marcações')
            . ' de "curso também obrigatório nesta área" '
            . ($totalVinculos === 1 ? 'some' : 'somem') . '.';
    }

    mse_json([
        'precisa_confirmar' => true,
        'id' => $id,
        'nome' => $area['name'],
        'consequencias' => $consequencias,
        'message' => $consequencias
            ? 'Excluir "' . $area['name'] . '" não tem volta.'
            : 'Excluir "' . $area['name'] . '" não tem volta. Nada mais depende dele.',
    ]);
}

// ------------------------------------------------------------
// Passo 2: exclui
// ------------------------------------------------------------
$stmt = $pdo->prepare('DELETE FROM areas WHERE id = ?');
$stmt->execute([$id]);

mse_json([
    'id' => $id,
    'nome' => $area['name'],
    'message' => 'Departamento "' . $area['name'] . '" excluído.',
]);
