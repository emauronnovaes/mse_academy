<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';

/**
 * Assuntos de um vídeo (os cartões da seção "Cursos": antes "áreas").
 *
 * Um mesmo vídeo pode estar em vários assuntos. courses.area_id continua
 * sendo o assunto principal (o primeiro escolhido); a tabela
 * course_assuntos guarda TODOS os assuntos do vídeo, inclusive o principal
 * (migração 031; a Academy cria a tabela sozinha se faltar).
 */

function mse_garantir_tabela_assuntos(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $st->execute(['course_assuntos']);
    if ($st->fetchColumn() !== false) {
        return $ok = true;
    }
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS course_assuntos (
               course_id INT UNSIGNED NOT NULL,
               area_id INT UNSIGNED NOT NULL,
               PRIMARY KEY (course_id, area_id),
               KEY idx_course_assuntos_area (area_id),
               CONSTRAINT fk_course_assuntos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
               CONSTRAINT fk_course_assuntos_area FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[course_assuntos] Não consegui criar a tabela (rode a migração 031): ' . $e->getMessage());
        return $ok = false;
    }
}

/** A tabela existe? (sem tentar criar; use nas leituras) */
function mse_tem_assuntos(PDO $pdo): bool
{
    static $tem = null;
    if ($tem === null) {
        $st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $st->execute(['course_assuntos']);
        $tem = $st->fetchColumn() !== false;
    }
    return $tem;
}

/**
 * Lê os assuntos pedidos (area_slugs, ou o area_slug antigo) e devolve os ids
 * na ordem escolhida, sem repetir. Slug que não existe é erro (mse_error).
 *
 * @return int[]
 */
function mse_ler_assuntos(PDO $pdo, array $input): array
{
    $slugs = [];
    if (isset($input['area_slugs']) && is_array($input['area_slugs'])) {
        $slugs = $input['area_slugs'];
    } elseif (trim((string) ($input['area_slug'] ?? '')) !== '') {
        $slugs = [$input['area_slug']];
    }
    $ids = [];
    $st = $pdo->prepare('SELECT id FROM areas WHERE slug = ?');
    foreach (array_slice($slugs, 0, 30) as $slug) {
        $slug = trim((string) $slug);
        if ($slug === '') {
            continue;
        }
        $st->execute([$slug]);
        $id = $st->fetchColumn();
        if ($id === false) {
            mse_error("O assunto \"{$slug}\" não existe.", 422);
        }
        $ids[(int) $id] = (int) $id;
    }
    return array_values($ids);
}

/** Grava TODOS os assuntos do vídeo (substitui os anteriores). O primeiro vira o principal. */
function mse_gravar_assuntos(PDO $pdo, int $courseId, array $areaIds): void
{
    if (!mse_garantir_tabela_assuntos($pdo)) {
        return; // sem a tabela, só vale o assunto principal (courses.area_id)
    }
    $pdo->prepare('DELETE FROM course_assuntos WHERE course_id = ?')->execute([$courseId]);
    $ins = $pdo->prepare('INSERT INTO course_assuntos (course_id, area_id) VALUES (?, ?)');
    foreach ($areaIds as $id) {
        $ins->execute([$courseId, (int) $id]);
    }
}

/**
 * Assuntos de vários vídeos de uma vez: course_id => [area_id, ...], com o
 * principal (courses.area_id) sempre incluído.
 *
 * @param int[] $courseIds
 * @return array<int, int[]>
 */
function mse_assuntos_mapa(PDO $pdo, array $courseIds): array
{
    $mapa = [];
    if (!$courseIds) {
        return $mapa;
    }
    $marc = implode(',', array_fill(0, count($courseIds), '?'));
    $st = $pdo->prepare("SELECT id, area_id FROM courses WHERE id IN ({$marc})");
    $st->execute(array_values($courseIds));
    foreach ($st->fetchAll() as $l) {
        if ($l['area_id'] !== null) {
            $mapa[(int) $l['id']][(int) $l['area_id']] = (int) $l['area_id'];
        }
    }
    if (mse_tem_assuntos($pdo)) {
        $st = $pdo->prepare("SELECT course_id, area_id FROM course_assuntos WHERE course_id IN ({$marc})");
        $st->execute(array_values($courseIds));
        foreach ($st->fetchAll() as $l) {
            $mapa[(int) $l['course_id']][(int) $l['area_id']] = (int) $l['area_id'];
        }
    }
    return array_map('array_values', $mapa);
}

/**
 * Assuntos de um vídeo com nome e slug, o principal primeiro.
 *
 * @return array<int, array{id: int, slug: string, name: string}>
 */
function mse_assuntos_do_curso(PDO $pdo, int $courseId): array
{
    $st = $pdo->prepare('SELECT area_id FROM courses WHERE id = ?');
    $st->execute([$courseId]);
    $principal = $st->fetchColumn();
    $ids = mse_assuntos_mapa($pdo, [$courseId])[$courseId] ?? [];
    if (!$ids) {
        return [];
    }
    $marc = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT id, slug, name FROM areas WHERE id IN ({$marc})");
    $st->execute($ids);
    $linhas = [];
    foreach ($st->fetchAll() as $l) {
        $linhas[(int) $l['id']] = ['id' => (int) $l['id'], 'slug' => $l['slug'], 'name' => $l['name']];
    }
    $saida = [];
    if ($principal !== null && isset($linhas[(int) $principal])) {
        $saida[] = $linhas[(int) $principal];
        unset($linhas[(int) $principal]);
    }
    foreach ($linhas as $l) {
        $saida[] = $l;
    }
    return $saida;
}
