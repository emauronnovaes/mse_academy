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

// Filtros opcionais — por nome (busca parcial) e por área/departamento
// (slug exato, vindo de um <select>).
$nomeFiltro = trim((string) ($_GET['nome'] ?? ''));
$areaFiltro = trim((string) ($_GET['area'] ?? ''));

$pdo = mse_db();

$totalOnboarding = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM courses WHERE type = 'onboarding' AND is_published = 1"
)->fetch()['c'];
$totalCursosDisponiveis = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM courses WHERE type = 'curso' AND is_published = 1"
)->fetch()['c'];

$where = ['u.active = 1'];
$params = [];
if ($nomeFiltro !== '') {
    $where[] = 'u.name LIKE :nome';
    $params[':nome'] = '%' . $nomeFiltro . '%';
}
if ($areaFiltro !== '') {
    $where[] = 'a.slug = :area';
    $params[':area'] = $areaFiltro;
}
$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare(
    "SELECT u.id, u.name, u.email, u.cargo, a.slug AS area_slug, a.name AS area_name,
            u.distinct_access_count, u.last_access_date,
            COUNT(CASE WHEN p.status = 'concluido' THEN 1 END) AS modules_done,
            MAX(p.completed_at) AS last_activity
     FROM users u
     LEFT JOIN areas a ON a.id = u.area_id
     LEFT JOIN user_course_progress p
            ON p.user_id = u.id
           AND p.course_id IN (SELECT id FROM courses WHERE type = 'onboarding')
     WHERE {$whereSql}
     GROUP BY u.id, u.name, u.email, u.cargo, a.slug, a.name, u.distinct_access_count, u.last_access_date
     ORDER BY u.name ASC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['id'] = (int) $row['id'];
    $row['modules_done'] = (int) $row['modules_done'];
    $row['distinct_access_count'] = (int) $row['distinct_access_count'];
    $row['total_onboarding_modules'] = $totalOnboarding;
    $row['completed'] = $totalOnboarding > 0 && $row['modules_done'] >= $totalOnboarding;
}

// Lista de áreas existentes — pra preencher o <select> do filtro no
// front-end, sem precisar de outra chamada separada.
$areas = $pdo->query('SELECT slug, name FROM areas ORDER BY name ASC')->fetchAll();

mse_json([
    'users' => $rows,
    'page' => $page,
    'per_page' => $perPage,
    'areas' => $areas,
    'total_cursos_disponiveis' => $totalCursosDisponiveis,
    'total_onboarding_modulos' => $totalOnboarding,
]);
