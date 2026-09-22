<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
$user = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

/**
 * Grava a duração real da aula, medida pelo player no navegador.
 *
 * O servidor não tem como saber isso sozinho: descobrir a duração de um
 * vídeo exigiria baixá-lo do S3 e analisá-lo, ou consultar a API do
 * YouTube com uma chave que o projeto não tem. Já o navegador sabe assim
 * que o vídeo carrega.
 *
 * Grava só uma vez (a primeira pessoa que abre a aula define o valor) —
 * assim um cliente adulterado não consegue ficar reescrevendo o tempo, e
 * a informação não fica oscilando a cada acesso.
 */

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$segundos = (int) round((float) ($input['segundos'] ?? 0));

if ($courseId <= 0) {
    mse_error('course_id é obrigatório.', 422);
}
// Acima de 65535 não cabe na coluna; abaixo de 1 não é duração.
if ($segundos < 1 || $segundos > 65535) {
    mse_error('Duração fora do intervalo aceito.', 422);
}

$pdo = mse_db();

// A migração pode ainda não ter rodado no servidor. Guardar a duração é
// um extra: não vale derrubar a aula com erro 500 por causa disso.
$temColuna = false;
foreach ($pdo->query('SHOW COLUMNS FROM courses') as $col) {
    if ($col['Field'] === 'duration_seconds') { $temColuna = true; break; }
}
if (!$temColuna) {
    mse_json(['ok' => true, 'gravado' => false, 'motivo' => 'coluna ainda não existe neste servidor']);
}

$stmt = $pdo->prepare(
    'UPDATE courses SET duration_seconds = ? WHERE id = ? AND duration_seconds IS NULL'
);
$stmt->execute([$segundos, $courseId]);

mse_json([
    'ok' => true,
    'gravado' => $stmt->rowCount() > 0, // false = outra pessoa já tinha registrado
]);
