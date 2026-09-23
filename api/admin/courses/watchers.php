<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

$pdo = mse_db();
$courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;

if ($courseId) {
    // Detalhe de UM vídeo específico: quem assistiu, com nome completo,
    // e-mail, cargo e quando concluiu — ordenado por mais recente primeiro.
    // Aceita filtro por nome (busca parcial) e por área/departamento.
    $stmt = $pdo->prepare(
        'SELECT c.id, c.title, c.type
         FROM courses c WHERE c.id = ? LIMIT 1'
    );
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    if (!$course) {
        mse_error('Curso não encontrado.', 404);
    }

    $nomeFiltro = trim((string) ($_GET['nome'] ?? ''));
    $areaFiltro = trim((string) ($_GET['area'] ?? ''));

    $where = ['p.course_id = :course_id', 'p.status <> "nao_iniciado"'];
    $params = [':course_id' => $courseId];
    if ($nomeFiltro !== '') {
        $where[] = 'u.name LIKE :nome';
        $params[':nome'] = '%' . $nomeFiltro . '%';
    }
    if ($areaFiltro !== '') {
        $where[] = 'ua.slug = :area';
        $params[':area'] = $areaFiltro;
    }
    // Mesmo filtro do relatório de acessos: quem está oculto não aparece
    // aqui também, senão a pessoa sumiria de uma lista e não da outra.
    if ($pdo->query("SHOW COLUMNS FROM users LIKE 'oculto_em_relatorios'")->fetch() !== false) {
        $where[] = 'u.oculto_em_relatorios = 0';
    }

    $whereSql = implode(' AND ', $where);

    $stmt = $pdo->prepare(
        "SELECT u.name, u.email, u.cargo, ua.name AS area_name, p.status, p.watched_pct, p.completed_at
         FROM user_course_progress p
         JOIN users u ON u.id = p.user_id
         LEFT JOIN areas ua ON ua.id = u.area_id
         WHERE {$whereSql}
         ORDER BY p.completed_at IS NULL, p.completed_at DESC, u.name ASC"
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $watchers = $stmt->fetchAll();

    // Lista de áreas — pra preencher o <select> do filtro no front-end.
    $areas = $pdo->query('SELECT slug, name FROM areas ORDER BY name ASC')->fetchAll();

    mse_json([
        'course' => ['id' => (int) $course['id'], 'title' => $course['title'], 'type' => $course['type']],
        'watchers' => array_map(function ($w) {
            return [
                'name' => $w['name'],           // nome completo
                'email' => $w['email'],
                'cargo' => $w['cargo'],
                'area_name' => $w['area_name'],
                'status' => $w['status'],
                'watched_pct' => (float) $w['watched_pct'],
                'completed_at' => $w['completed_at'],
            ];
        }, $watchers),
        'total' => count($watchers),
        'areas' => $areas,
    ]);
} else {
    // Visão geral: todos os vídeos, com a contagem de quem assistiu cada um.
    $stmt = $pdo->query(
        'SELECT c.id, c.title, c.type, c.is_published, a.name AS area_name,
                COUNT(CASE WHEN p.status = "concluido" THEN 1 END) AS total_concluido,
                COUNT(CASE WHEN p.status = "em_andamento" THEN 1 END) AS total_em_andamento
         FROM courses c
         LEFT JOIN areas a ON a.id = c.area_id
         LEFT JOIN user_course_progress p ON p.course_id = c.id
         GROUP BY c.id, c.title, c.type, c.is_published, a.name
         ORDER BY c.type, c.order_index'
    );
    $courses = $stmt->fetchAll();

    mse_json([
        'courses' => array_map(function ($c) {
            return [
                'id' => (int) $c['id'],
                'title' => $c['title'],
                'type' => $c['type'],
                'area_name' => $c['area_name'],
                'is_published' => (int) $c['is_published'],
                'total_concluido' => (int) $c['total_concluido'],
                'total_em_andamento' => (int) $c['total_em_andamento'],
            ];
        }, $courses),
    ]);
}
