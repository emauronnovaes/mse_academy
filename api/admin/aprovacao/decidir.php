<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';

/**
 * Aprova ou recusa um vídeo enviado para aprovação.
 *
 * Aprovar publica na hora (is_published = 1), como se um admin tivesse
 * cadastrado. Recusar mantém escondido e guarda o motivo — nada é apagado.
 * Um vídeo recusado ainda pode ser aprovado depois (o histórico de
 * recusados fica na tela de aprovação). Se dois admins decidirem ao mesmo
 * tempo, vale o primeiro.
 *
 * POST {course_id, acao: 'aprovar' | 'recusar', motivo?}
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$acao = (string) ($input['acao'] ?? '');
$motivo = mb_substr(trim((string) ($input['motivo'] ?? '')), 0, 500);
if ($courseId <= 0 || !in_array($acao, ['aprovar', 'recusar'], true)) {
    mse_error('Informe course_id e acao ("aprovar" ou "recusar").', 422);
}

$pdo = mse_db();
if (!mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    mse_error('Vídeo não encontrado.', 404);
}

$stmt = $pdo->prepare(
    $acao === 'aprovar'
        ? "UPDATE courses SET is_published = 1, aprovacao_status = 'aprovado', decidido_por = ?, decidido_em = NOW(), motivo_recusa = NULL
           WHERE id = ? AND aprovacao_status IN ('pendente', 'recusado')"
        : "UPDATE courses SET is_published = 0, aprovacao_status = 'recusado', decidido_por = ?, decidido_em = NOW(), motivo_recusa = ?
           WHERE id = ? AND aprovacao_status = 'pendente'"
);
$stmt->execute($acao === 'aprovar' ? [(int) $admin['id'], $courseId] : [(int) $admin['id'], $motivo ?: null, $courseId]);

if ($stmt->rowCount() === 0) {
    $stmt = $pdo->prepare(
        'SELECT c.aprovacao_status, u.name FROM courses c LEFT JOIN users u ON u.id = c.decidido_por WHERE c.id = ?'
    );
    $stmt->execute([$courseId]);
    $atual = $stmt->fetch();
    if (!$atual || $atual['aprovacao_status'] === null) {
        mse_error('Vídeo não encontrado.', 404);
    }
    $foi = $atual['aprovacao_status'] === 'aprovado' ? 'aprovado' : 'recusado';
    mse_error("Esse vídeo já foi {$foi}" . ($atual['name'] ? " por {$atual['name']}" : '') . '.', 409);
}

mse_json(['ok' => true, 'status' => $acao === 'aprovar' ? 'aprovado' : 'recusado']);
