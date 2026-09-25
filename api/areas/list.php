<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();

/**
 * Lista os departamentos da seção "Cursos".
 *
 * Antes isto servia só ao seletor de área do painel e exigia admin. Agora
 * os cartões da tela também saem daqui: eles eram 22 blocos escritos à mão
 * no index.html, então departamento criado no banco não aparecia e nome
 * trocado no banco não mudava na tela.
 *
 * Quem não é admin recebe só os departamentos que têm pelo menos um vídeo
 * publicado. A regra existe porque um cartão sem aula nenhuma abre numa
 * tela vazia — era o caso de 21 dos 22. E é uma regra, não uma lista
 * escolhida à mão: no dia em que um vídeo for publicado num departamento,
 * ele volta a aparecer sozinho, sem precisar lembrar de ligar nada.
 *
 * Admin recebe todos, com a contagem de vídeos de cada um, pra conseguir
 * preparar um departamento antes de ele ter conteúdo.
 */

$user = mse_require_auth();
$ehAdmin = ($user['role'] ?? '') === 'admin';
$pdo = mse_db();

// O código sobe antes de a migração rodar no servidor. Sem esta checagem,
// a seção "Cursos" ficaria vazia entre o deploy e a migração — foi o que
// aconteceu com a trilha quando duration_seconds chegou antes da hora.
$temColunasNovas = $pdo->query("SHOW COLUMNS FROM areas LIKE 'icon'")->fetch() !== false;

$campos = $temColunasNovas
    ? 'a.id, a.slug, a.name, a.icon, a.descricao, a.ordem'
    : "a.id, a.slug, a.name, 'fa-folder-open' AS icon, '' AS descricao, 0 AS ordem";

$ordenacao = $temColunasNovas ? 'a.ordem ASC, a.name ASC' : 'a.name ASC';

$sql = "SELECT {$campos},
               (SELECT COUNT(*) FROM courses c
                 WHERE c.area_id = a.id AND c.type = 'curso' AND c.is_published = 1) AS total_cursos
        FROM areas a
        ORDER BY {$ordenacao}";

$areas = $pdo->query($sql)->fetchAll();

$lista = [];
foreach ($areas as $a) {
    $total = (int) $a['total_cursos'];
    if (!$ehAdmin && $total === 0) {
        continue;
    }
    $lista[] = [
        'id' => (int) $a['id'],
        'slug' => $a['slug'],
        'name' => $a['name'],
        'icon' => $a['icon'] !== '' ? $a['icon'] : 'fa-folder-open',
        'descricao' => $a['descricao'],
        'ordem' => (int) $a['ordem'],
        'total_cursos' => $total,
    ];
}

mse_json([
    'areas' => $lista,
    'admin' => $ehAdmin,
    'migracao_pendente' => !$temColunasNovas,
]);
