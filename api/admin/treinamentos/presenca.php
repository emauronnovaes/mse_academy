<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';

/**
 * Lista de presença de um treinamento — preenchida sozinha a partir do uso
 * da Academy, sem ninguém digitar nada:
 *   - nome, CPF, e-mail, cargo e departamento: vêm do Portal no login (SSO)
 *   - check-in: quando a pessoa abriu o vídeo pela primeira vez
 *   - check-out: quando concluiu
 *   - % assistido e tempo estimado
 *   - confirmações de "Estou aqui" durante o vídeo
 *   - acertos nas perguntas e atividades
 *
 * GET ?course_id=N [&q=nome/CPF] [&data_de=AAAA-MM-DD] [&data_ate=AAAA-MM-DD]
 */

mse_cors();
mse_require_admin();

$courseId = (int) ($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$pdo = mse_db();
$stmt = $pdo->prepare('SELECT id, title, type, is_published FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$curso = $stmt->fetch();
if (!$curso) {
    mse_error('Treinamento não encontrado.', 404);
}

mse_json([
    'treinamento' => [
        'id' => (int) $curso['id'],
        'tema' => $curso['title'],
        'arquivado' => (int) $curso['is_published'] === 0,
    ],
    'checkin_disponivel' => mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em'),
    'participantes' => mse_lista_presenca($pdo, $courseId, [
        'q' => (string) ($_GET['q'] ?? ''),
        'data_de' => (string) ($_GET['data_de'] ?? ''),
        'data_ate' => (string) ($_GET['data_ate'] ?? ''),
    ]),
]);
