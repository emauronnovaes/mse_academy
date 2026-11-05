<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';

/**
 * Ordem das aulas da trilha de Integração.
 *
 * GET               lista as aulas de integração na ordem atual
 * POST {ids: [...]} grava a ordem nova (1, 2, 3... na ordem recebida)
 *
 * A ordem decide o que a pessoa vê primeiro e o que destrava o quê: uma aula
 * só abre depois das obrigatórias que vêm antes dela. O que já foi concluído
 * continua concluído.
 */

mse_cors();
mse_require_admin();
$pdo = mse_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_values(array_unique(array_map('intval', (array) (mse_input()['ids'] ?? []))));
    if (!$ids) {
        mse_error('Mande a lista de aulas na ordem nova (ids).', 422);
    }
    $st = $pdo->prepare('SELECT id FROM courses WHERE type = ? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $st->execute(array_merge(['onboarding'], $ids));
    if (count($st->fetchAll()) !== count($ids)) {
        mse_error('A lista tem aula que não é da integração (ou que não existe mais). Recarregue a tela.', 422);
    }
    $pdo->beginTransaction();
    try {
        $up = $pdo->prepare("UPDATE courses SET order_index = ? WHERE id = ? AND type = 'onboarding'");
        foreach ($ids as $i => $id) {
            $up->execute([$i + 1, $id]);
        }
        // Alguma aula de integração que não veio na lista vai para o fim, na ordem em que estava.
        $resto = $pdo->prepare("SELECT id FROM courses WHERE type = 'onboarding' AND id NOT IN (" . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY order_index, id');
        $resto->execute($ids);
        $n = count($ids);
        foreach ($resto->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $up->execute([++$n, (int) $id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    mse_json(['ok' => true]);
}

$cols = ['c.id', 'c.title', 'c.order_index', 'c.is_published'];
$cols[] = mse_tem_coluna($pdo, 'courses', 'obrigatorio') ? 'c.obrigatorio' : '1 AS obrigatorio';
$cols[] = mse_tem_coluna($pdo, 'courses', 'grupo_sorteio') ? 'c.grupo_sorteio' : 'NULL AS grupo_sorteio';
$cols[] = mse_tem_coluna($pdo, 'courses', 'somente_obra') ? 'c.somente_obra' : '0 AS somente_obra';
$cols[] = mse_tem_coluna($pdo, 'courses', 'somente_sede') ? 'c.somente_sede' : '0 AS somente_sede';
$aulas = $pdo->query('SELECT ' . implode(', ', $cols) . " FROM courses c WHERE c.type = 'onboarding' ORDER BY c.order_index, c.id")->fetchAll();

mse_json(['aulas' => array_map(static fn($a) => [
    'id' => (int) $a['id'],
    'titulo' => $a['title'],
    'publicada' => (int) $a['is_published'] === 1,
    'obrigatorio' => (int) $a['obrigatorio'] === 1,
    'grupo_sorteio' => $a['grupo_sorteio'] ?: null,
    'publico' => $a['somente_obra'] ? 'obra' : ($a['somente_sede'] ? 'sede' : 'todos'),
], $aulas)]);
