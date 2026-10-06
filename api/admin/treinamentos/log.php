<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';

/**
 * Log de presença de uma pessoa (check-ins, check-outs e "Estou aqui").
 *
 * GET ?user_id=N [&course_id=N] [&data_de=AAAA-MM-DD] [&data_ate=AAAA-MM-DD]
 */

mse_cors();
mse_require_admin();

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    mse_error('Informe user_id.', 422);
}
$courseId = (int) ($_GET['course_id'] ?? 0);

$pdo = mse_db();
mse_json([
    'log_disponivel' => mse_tem_tabela($pdo, 'presenca_log'),
    'eventos' => mse_log_presenca($pdo, $userId, $courseId ?: null, [
        'data_de' => (string) ($_GET['data_de'] ?? ''),
        'data_ate' => (string) ($_GET['data_ate'] ?? ''),
    ]),
]);
