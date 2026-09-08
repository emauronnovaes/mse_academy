<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Normaliza texto pra comparar cargo/palavra-chave sem se importar com
 * acento, maiúscula/minúscula ou espaço a mais. "Análise Financeira Jr"
 * e "analise   financeira jr" viram a mesma coisa.
 */
function mse_normalize_text(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    // remove acentos (transliteração) — funciona sem precisar da
    // extensão intl, só com iconv (padrão em praticamente todo PHP)
    $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($transliterated !== false) {
        $text = $transliterated;
    }
    $text = preg_replace('/[^a-z0-9\s]/', '', $text) ?? $text;
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

/**
 * true se alguma keyword de cargo associada ao curso aparece dentro do
 * cargo da pessoa (comparação normalizada, "contém", não "é igual a").
 */
function mse_cargo_matches_course(PDO $pdo, string $cargo, int $courseId): bool
{
    if (trim($cargo) === '') {
        return false;
    }
    $normalizedCargo = mse_normalize_text($cargo);

    $stmt = $pdo->prepare('SELECT keyword FROM course_cargo_keywords WHERE course_id = ?');
    $stmt->execute([$courseId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $keyword) {
        if ($keyword !== '' && str_contains($normalizedCargo, $keyword)) {
            return true;
        }
    }
    return false;
}
/**
 * Verifica se o usuário pode assistir/responder este módulo.
 *
 * - Cursos do tipo "curso" (catálogo livre, aba Cursos): sempre liberado.
 * - Cursos do tipo "onboarding" (trilha obrigatória): só libera se TODOS
 *   os módulos anteriores (order_index menor) já estiverem "concluido"
 *   para esse usuário. Essa checagem roda no servidor a cada chamada —
 *   não dá pra burlar mandando o course_id "errado" direto pela API.
 */
function mse_course_is_unlocked(PDO $pdo, int $userId, int $courseId): bool
{
    $stmt = $pdo->prepare('SELECT type, order_index FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();

    if (!$course) {
        return false;
    }
    if ($course['type'] !== 'onboarding') {
        return true;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM courses WHERE type = 'onboarding' AND order_index < ?"
    );
    $stmt->execute([$course['order_index']]);
    $previousIds = array_column($stmt->fetchAll(), 'id');

    if (empty($previousIds)) {
        return true; // é o primeiro módulo da trilha
    }

    $placeholders = implode(',', array_fill(0, count($previousIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total FROM user_course_progress
         WHERE user_id = ? AND course_id IN ({$placeholders}) AND status = 'concluido'"
    );
    $stmt->execute([$userId, ...$previousIds]);
    $row = $stmt->fetch();

    return (int) $row['total'] === count($previousIds);
}
