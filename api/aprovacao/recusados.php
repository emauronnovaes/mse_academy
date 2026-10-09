<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Aprovacao.php';

/**
 * Avisos de vídeo recusado, para quem enviou.
 *
 * GET  → os vídeos recusados desta pessoa que ela ainda não viu, cada um
 *        com o motivo que o admin escreveu. A tela mostra num pop-up.
 * POST {course_id} → marca como visto (o pop-up não volta).
 *
 * Só enxerga e marca os próprios envios.
 */

mse_cors();
$usuario = mse_require_auth();

$pdo = mse_db();
// Sem a coluna, ninguém enviou vídeo ainda: nada a avisar.
if (!mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    mse_json(['recusados' => []]);
}
// Instalações que rodaram a migração 024 antes da coluna "recusa_vista_em"
// existir ganham ela aqui (coluna vazia, não mexe em dado nenhum).
if (!mse_garantir_colunas_aprovacao($pdo)) {
    mse_json(['recusados' => []]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int) (mse_input()['course_id'] ?? 0);
    if ($courseId <= 0) {
        mse_error('Informe course_id.', 422);
    }
    $pdo->prepare(
        "UPDATE courses SET recusa_vista_em = NOW()
         WHERE id = ? AND enviado_por = ? AND aprovacao_status = 'recusado' AND recusa_vista_em IS NULL"
    )->execute([$courseId, (int) $usuario['id']]);
    mse_json(['ok' => true]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    mse_error('Método não permitido.', 405);
}

// Colunas criadas na mesma requisição não aparecem no cache de mse_tem_coluna,
// por isso a consulta usa só colunas que a própria garantia acabou de criar.
$stmt = $pdo->prepare(
    "SELECT c.id, c.title, c.motivo_recusa, c.decidido_em, c.enviado_em
     FROM courses c
     WHERE c.enviado_por = ? AND c.aprovacao_status = 'recusado' AND c.recusa_vista_em IS NULL
     ORDER BY c.decidido_em ASC, c.id ASC
     LIMIT 20"
);
$stmt->execute([(int) $usuario['id']]);

mse_json(['recusados' => array_map(static fn($c) => [
    'id' => (int) $c['id'],
    'titulo' => $c['title'],
    'motivo' => trim((string) $c['motivo_recusa']) !== '' ? trim((string) $c['motivo_recusa']) : null,
    'decidido_em' => $c['decidido_em'],
    'enviado_em' => $c['enviado_em'],
], $stmt->fetchAll())]);
