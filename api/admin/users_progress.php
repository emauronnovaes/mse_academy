<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
mse_require_admin();

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$pdo = mse_db();

$totalOnboarding = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM courses WHERE type = 'onboarding' AND is_published = 1"
)->fetch()['c'];

$stmt = $pdo->prepare(
    "SELECT u.id, u.name, u.email, a.name AS area_name,
            COUNT(CASE WHEN p.status = 'concluido' THEN 1 END) AS modules_done,
            MAX(p.completed_at) AS last_activity
     FROM users u
     LEFT JOIN areas a ON a.id = u.area_id
     LEFT JOIN user_course_progress p
            ON p.user_id = u.id
           AND p.course_id IN (SELECT id FROM courses WHERE type = 'onboarding')
     WHERE u.active = 1
     GROUP BY u.id, u.name, u.email, a.name
     ORDER BY u.name ASC
     LIMIT :limit OFFSET :offset"
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['id'] = (int) $row['id'];
    $row['modules_done'] = (int) $row['modules_done'];
    $row['total_onboarding_modules'] = $totalOnboarding;
    $row['completed'] = $totalOnboarding > 0 && $row['modules_done'] >= $totalOnboarding;
}

mse_json(['users' => $rows, 'page' => $page, 'per_page' => $perPage]);
