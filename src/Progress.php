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
        if ($keyword !== '' && mse_str_contains($normalizedCargo, $keyword)) {
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
/**
 * Resolve quais aulas da trilha ESTA pessoa deve ver.
 *
 * Aula sem grupo_sorteio todo mundo vê. Aulas que compartilham o mesmo
 * grupo_sorteio são alternativas entre si: só uma é sorteada por pessoa,
 * e a escolha fica gravada — sortear a cada acesso faria a pessoa cair
 * num vídeo diferente toda vez, jogando fora o progresso do anterior.
 *
 * @return array<int, array{id:int, obrigatorio:int, order_index:int}>
 */
function mse_aulas_da_trilha(PDO $pdo, int $userId): array
{
    $stmt = $pdo->query(
        "SELECT id, grupo_sorteio, obrigatorio, order_index
         FROM courses
         WHERE type = 'onboarding' AND is_published = 1
         ORDER BY order_index ASC, id ASC"
    );
    $todas = $stmt->fetchAll();

    $semGrupo = [];
    $porGrupo = [];
    foreach ($todas as $c) {
        if ($c['grupo_sorteio'] === null || $c['grupo_sorteio'] === '') {
            $semGrupo[] = $c;
        } else {
            $porGrupo[$c['grupo_sorteio']][] = $c;
        }
    }

    $escolhidas = $semGrupo;

    foreach ($porGrupo as $grupo => $alternativas) {
        $idsValidos = array_column($alternativas, 'id');

        $stmt = $pdo->prepare('SELECT course_id FROM user_sorteio_aula WHERE user_id = ? AND grupo = ?');
        $stmt->execute([$userId, $grupo]);
        $jaSorteado = $stmt->fetchColumn();

        // Se a aula sorteada foi apagada ou despublicada depois, sorteia
        // de novo em vez de deixar a pessoa sem nada naquele grupo.
        if ($jaSorteado && in_array((int) $jaSorteado, $idsValidos, true)) {
            $escolhidoId = (int) $jaSorteado;
        } else {
            $escolhidoId = $idsValidos[random_int(0, count($idsValidos) - 1)];
            $stmt = $pdo->prepare(
                'INSERT INTO user_sorteio_aula (user_id, grupo, course_id) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE course_id = VALUES(course_id)'
            );
            $stmt->execute([$userId, $grupo, $escolhidoId]);
        }

        foreach ($alternativas as $alt) {
            if ((int) $alt['id'] === $escolhidoId) {
                $escolhidas[] = $alt;
                break;
            }
        }
    }

    usort($escolhidas, static fn($a, $b) => [$a['order_index'], $a['id']] <=> [$b['order_index'], $b['id']]);

    return array_map(static fn($c) => [
        'id' => (int) $c['id'],
        'obrigatorio' => (int) $c['obrigatorio'],
        'order_index' => (int) $c['order_index'],
    ], $escolhidas);
}

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

    // Só contam as aulas que ESTA pessoa vê, e só as obrigatórias. Sem
    // isso, as alternativas não sorteadas (que ela nunca vai ver) e as
    // opcionais travariam a trilha inteira pra sempre.
    $minhas = mse_aulas_da_trilha($pdo, $userId);
    $previousIds = [];
    foreach ($minhas as $aula) {
        if ($aula['order_index'] < (int) $course['order_index'] && $aula['obrigatorio'] === 1) {
            $previousIds[] = $aula['id'];
        }
    }

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
