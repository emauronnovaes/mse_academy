<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
$user = mse_require_auth();

$pdo = mse_db();
$stmt = $pdo->prepare(
    'SELECT course_id, status, watched_pct, completed_at
     FROM user_course_progress
     WHERE user_id = ?'
);
$stmt->execute([$user['id']]);
$progress = $stmt->fetchAll();

foreach ($progress as &$row) {
    $row['course_id'] = (int) $row['course_id'];
    $row['watched_pct'] = (float) $row['watched_pct'];
}

// Aulas mudaram: o banco de consulta se atualiza em até 1 minuto.
require_once __DIR__ . '/../../src/Viniconsultas.php';
mse_vini_recalcular_agendado();

mse_json(['progress' => $progress]);
