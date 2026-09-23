<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

mse_cors();
mse_require_admin();

/**
 * Mapeia cargo → área. Quando o setor da pessoa não vem nem do RH nem
 * do token do Portal, é daqui que ele é deduzido.
 *
 * GET                          → lista o mapeamento + as áreas disponíveis
 * POST {palavra, area_id}      → acrescenta
 * POST {remover_id}            → remove
 * POST {testar}                → diz qual área um cargo cairia (sem gravar)
 */

$pdo = mse_db();

if ($pdo->query("SHOW TABLES LIKE 'cargo_areas'")->fetch() === false) {
    mse_error('Este servidor ainda não tem a tabela de cargos por área. Falta rodar a migração 017 no banco (migrations/017_cargo_para_area.sql).', 409);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $regras = [];
    $sql = 'SELECT ca.id, ca.palavra, ca.area_id, a.name AS area_name
            FROM cargo_areas ca JOIN areas a ON a.id = ca.area_id
            ORDER BY a.name ASC, ca.palavra ASC';
    foreach ($pdo->query($sql) as $r) {
        $regras[] = [
            'id' => (int) $r['id'],
            'palavra' => $r['palavra'],
            'area_id' => (int) $r['area_id'],
            'area_name' => $r['area_name'],
        ];
    }

    $areas = [];
    foreach ($pdo->query('SELECT id, name FROM areas ORDER BY name ASC') as $a) {
        $areas[] = ['id' => (int) $a['id'], 'name' => $a['name']];
    }

    // Cargos que já apareceram, pra facilitar: em vez de adivinhar como
    // o RH escreve, o admin vê o que existe de verdade e se cada um já
    // está coberto por alguma regra.
    $cargos = [];
    foreach ($pdo->query("SELECT DISTINCT cargo FROM users WHERE cargo IS NOT NULL AND cargo <> '' ORDER BY cargo") as $c) {
        $areaId = mse_area_id_por_cargo($pdo, $c['cargo']);
        $cargos[] = [
            'cargo' => $c['cargo'],
            'area_id' => $areaId,
            'area_name' => $areaId
                ? $pdo->query('SELECT name FROM areas WHERE id = ' . $areaId)->fetchColumn()
                : null,
        ];
    }

    mse_json(['regras' => $regras, 'areas' => $areas, 'cargos_existentes' => $cargos]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();

if (!empty($input['remover_id'])) {
    $stmt = $pdo->prepare('DELETE FROM cargo_areas WHERE id = ?');
    $stmt->execute([(int) $input['remover_id']]);
    mse_json(['removido' => $stmt->rowCount() > 0]);
}

if (!empty($input['testar'])) {
    $areaId = mse_area_id_por_cargo($pdo, (string) $input['testar']);
    mse_json([
        'cargo' => (string) $input['testar'],
        'area_id' => $areaId,
        'area_name' => $areaId
            ? $pdo->query('SELECT name FROM areas WHERE id = ' . $areaId)->fetchColumn()
            : null,
    ]);
}

$palavra = trim((string) ($input['palavra'] ?? ''));
$areaId = (int) ($input['area_id'] ?? 0);

if ($palavra === '' || $areaId <= 0) {
    mse_error('Informe a palavra do cargo e a área.', 422);
}
if (mb_strlen($palavra) > 80) {
    mse_error('Palavra longa demais (máximo 80 caracteres).', 422);
}

$stmt = $pdo->prepare('SELECT id FROM areas WHERE id = ?');
$stmt->execute([$areaId]);
if (!$stmt->fetch()) {
    mse_error('Área não encontrada.', 422);
}

// Guarda já normalizada (sem acento, minúscula): é assim que a
// comparação é feita no login, e gravar o texto cru faria a regra nunca
// casar quando o admin digitasse com acento.
$normalizada = mse_normalize_text($palavra);
if ($normalizada === '') {
    mse_error('Palavra inválida.', 422);
}

$stmt = $pdo->prepare('INSERT IGNORE INTO cargo_areas (palavra, area_id) VALUES (?, ?)');
$stmt->execute([$normalizada, $areaId]);

mse_json([
    'palavra' => $normalizada,
    'area_id' => $areaId,
    'ja_existia' => $stmt->rowCount() === 0,
    'message' => $stmt->rowCount() === 0
        ? 'Essa regra já existia.'
        : 'Regra criada: cargo contendo "' . $normalizada . '" passa a ser dessa área.',
]);
