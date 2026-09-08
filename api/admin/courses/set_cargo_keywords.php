<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';

mse_cors();
mse_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$keywords = $input['keywords'] ?? null; // array de strings, ex: ["financeiro", "analista"]

if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}
if (!is_array($keywords)) {
    mse_error('Informe keywords como uma lista de palavras (pode ser vazia, pra limpar tudo).', 422);
}

$pdo = mse_db();

$stmt = $pdo->prepare('SELECT id FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
if (!$stmt->fetch()) {
    mse_error('Curso não encontrado.', 404);
}

// Normaliza, remove vazias/duplicadas — a mesma normalização usada na
// hora de COMPARAR com o cargo da pessoa (mse_normalize_text), senão
// uma keyword salva com acento nunca bateria com nada.
$normalized = [];
foreach ($keywords as $kw) {
    $n = mse_normalize_text((string) $kw);
    if ($n !== '') {
        $normalized[$n] = true; // usa como chave só pra deduplicar de graça
    }
}
$normalized = array_keys($normalized);

// Substitui a lista inteira (mais simples e previsível pro admin do que
// "adicionar uma de cada vez" — ele manda a lista final que quer).
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('DELETE FROM course_cargo_keywords WHERE course_id = ?');
    $stmt->execute([$courseId]);

    $stmt = $pdo->prepare('INSERT INTO course_cargo_keywords (course_id, keyword) VALUES (?, ?)');
    foreach ($normalized as $kw) {
        $stmt->execute([$courseId, $kw]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Falha ao salvar. Nada foi gravado. Detalhe: ' . $e->getMessage(), 500);
}

mse_json([
    'course_id' => $courseId,
    'keywords' => $normalized,
    'message' => count($normalized) > 0
        ? 'Cargos com essas palavras vão ver esse curso na recomendação, mesmo se forem de outra área.'
        : 'Nenhuma palavra-chave — esse curso só aparece pela recomendação por área agora.',
]);
