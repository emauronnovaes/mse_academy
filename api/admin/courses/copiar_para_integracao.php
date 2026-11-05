<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Classificacao.php';

/**
 * Coloca um vídeo de Cursos também na trilha de Integração: cria uma CÓPIA
 * do vídeo (mesmo arquivo/link, mesmas perguntas e atividades) como aula de
 * integração, para o público escolhido (padrão: só obra). O curso original
 * continua no catálogo como está.
 *
 * O progresso é separado: assistir a cópia na integração não conclui o curso,
 * e vice-versa.
 *
 * POST {course_id, publico?: 'todos'|'sede'|'obra'}
 */

mse_cors();
mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$in = mse_input();
$courseId = (int) ($in['course_id'] ?? 0);
$publico = mse_ler_publico(['publico' => (string) ($in['publico'] ?? 'obra')]);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$pdo = mse_db();
// Antes da transação: criar coluna (ALTER) encerraria a transação aberta.
if (!mse_garantir_colunas_classificacao($pdo)) {
    mse_error('Falta rodar a migração 053 no banco (migrations/053_videos_sede_ou_obra.sql).', 409);
}

$st = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$st->execute([$courseId]);
$orig = $st->fetch();
if (!$orig) {
    mse_error('Vídeo não encontrado.', 404);
}
if ($orig['type'] !== 'curso') {
    mse_error('Esse vídeo já é da integração.', 422);
}
if (!empty($orig['aprovacao_status']) && $orig['aprovacao_status'] !== 'aprovado') {
    mse_error('Esse vídeo ainda não foi aprovado. Aprove antes de colocar na integração.', 422);
}

// Copia todas as colunas, menos as que são do catálogo, da aprovação ou geradas.
$fora = ['id', 'created_at', 'updated_at', 'area_id', 'grupo_sorteio', 'palavras_chave', 'obrigatoriedade',
    'tema_sugerido', 'tema_status', 'aprovacao_status', 'enviado_por', 'enviado_em', 'notificado_em',
    'decidido_por', 'decidido_em', 'motivo_recusa', 'recusa_vista_em'];
$novo = array_diff_key($orig, array_flip($fora));
$novo['type'] = 'onboarding';
$novo['obrigatorio'] = 1;
$novo['is_published'] = 1;
$novo['order_index'] = (int) $pdo->query("SELECT COALESCE(MAX(order_index), 0) + 1 FROM courses WHERE type = 'onboarding'")->fetchColumn();
// Prova do crachá (NR-06/NR-12) fica só no original.
if (array_key_exists('codigo_nr', $novo)) {
    $novo['codigo_nr'] = null;
}
$novo = array_merge($novo, $publico);

$pdo->beginTransaction();
try {
    $cols = array_keys($novo);
    $pdo->prepare('INSERT INTO courses (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
        ->execute(array_values($novo));
    $novoId = (int) $pdo->lastInsertId();

    // Perguntas do fim e atividades durante o vídeo, com as opções.
    $temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
    $qs = $pdo->prepare('SELECT * FROM quiz_questions WHERE course_id = ? ORDER BY id');
    $qs->execute([$courseId]);
    $insQ = $pdo->prepare('INSERT INTO quiz_questions (course_id, question_text, order_index' . ($temMomento ? ', momento_seg' : '') . ') VALUES (?, ?, ?' . ($temMomento ? ', ?' : '') . ')');
    $ops = $pdo->prepare('SELECT option_text, is_correct, order_index FROM quiz_options WHERE question_id = ? ORDER BY id');
    $insO = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES (?, ?, ?, ?)');
    $perguntas = 0;
    foreach ($qs->fetchAll() as $q) {
        $insQ->execute(array_merge([$novoId, $q['question_text'], $q['order_index']], $temMomento ? [$q['momento_seg']] : []));
        $novaQ = (int) $pdo->lastInsertId();
        $ops->execute([(int) $q['id']]);
        foreach ($ops->fetchAll() as $o) {
            $insO->execute([$novaQ, $o['option_text'], $o['is_correct'], $o['order_index']]);
        }
        $perguntas++;
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

mse_json(['ok' => true, 'course_id' => $novoId, 'perguntas' => $perguntas]);
