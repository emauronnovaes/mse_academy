<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
mse_require_admin();

// Existe pra que o seletor de área (ao cadastrar vídeo) não precise de
// uma lista fixa no JavaScript — antes ela tinha 8 das 22 áreas, então
// 14 áreas do banco eram impossíveis de escolher pela interface.
$areas = mse_db()->query('SELECT slug, name FROM areas ORDER BY name ASC')->fetchAll();

mse_json([
    'areas' => array_map(function ($a) {
        return ['slug' => $a['slug'], 'name' => $a['name']];
    }, $areas),
]);
