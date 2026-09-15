<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

mse_cors();
$user = mse_require_auth();

$type = $_GET['type'] ?? null;       // 'onboarding' ou 'curso'
$areaSlug = $_GET['area'] ?? null;   // filtro explícito por área (ex: 'financeiro')
$scope = $_GET['scope'] ?? 'recommended'; // 'recommended' (padrão) ou 'all'

$pdo = mse_db();

$sql = "SELECT c.id, c.title, c.description, c.youtube_id, c.duration_minutes,
               c.order_index, c.type, c.area_id, a.slug AS area_slug, a.name AS area_name
        FROM courses c
        LEFT JOIN areas a ON a.id = c.area_id
        WHERE c.is_published = 1";
$params = [];

if ($type === 'onboarding' || $type === 'curso') {
    $sql .= ' AND c.type = ?';
    $params[] = $type;
}

if ($areaSlug) {
    // Filtro explícito manda mais que a recomendação automática
    $sql .= ' AND a.slug = ?';
    $params[] = $areaSlug;
}

$sql .= ' ORDER BY c.order_index ASC, c.id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Recomendação automática (só quando não há filtro explícito de área):
// um curso "sobe" pra recomendado se é da MESMA ÁREA da pessoa OU se
// tem uma palavra-chave de cargo que bate com o cargo dela — mesmo que
// seja de outra área (ex: um "Analista Financeiro" lotado na área de
// Obras ainda vê os cursos de Financeiro, por causa do cargo).
// A trilha de integração (onboarding) nunca é filtrada — obrigatória
// pra todo mundo, de qualquer área/cargo.
if (!$areaSlug && $scope === 'recommended' && $type !== 'onboarding') {
    $courses = array_values(array_filter($courses, function ($course) use ($user, $pdo) {
        $matchesArea = $user['area_id'] && (int) $course['area_id'] === (int) $user['area_id'];
        $matchesCargo = !empty($user['cargo']) && mse_cargo_matches_course($pdo, $user['cargo'], (int) $course['id']);
        return $matchesArea || $matchesCargo;
    }));
}

foreach ($courses as &$course) {
    $course['id'] = (int) $course['id'];
    $course['area_id'] = $course['area_id'] !== null ? (int) $course['area_id'] : null;
    $course['duration_minutes'] = (int) $course['duration_minutes'];
    $course['order_index'] = (int) $course['order_index'];
}

mse_json([
    'courses' => $courses,
    'scope' => $areaSlug ? 'area:' . $areaSlug : $scope,
]);
