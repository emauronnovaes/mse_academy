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
 * Recusar apaga o arquivo do vídeo da AWS (⚠ irreversível): o registro
 * (título, perguntas, motivo) fica no histórico de recusados, mas aprovar
 * depois só é possível se o arquivo ainda existir (YouTube, ou o envio que
 * falhou em apagar). Um vídeo recusado ainda pode ser aprovado depois (o
 * histórico de recusados fica na tela de aprovação). Se dois admins decidirem ao mesmo
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

// Vídeo recusado cujo arquivo já foi apagado da AWS não pode ser aprovado.
if ($acao === 'aprovar') {
    $stmt = $pdo->prepare('SELECT video_source, video_key, aprovacao_status FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $c = $stmt->fetch();
    if ($c && $c['aprovacao_status'] === 'recusado' && $c['video_source'] === 's3' && trim((string) $c['video_key']) === '') {
        mse_error('O arquivo deste vídeo foi apagado da AWS quando ele foi recusado. Peça para a pessoa enviar o vídeo de novo.', 409);
    }
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

// Recusou: o arquivo do vídeo é apagado da AWS (⚠ irreversível — ver mse_excluir_arquivo_recusado).
$arquivo = $acao === 'recusar' ? mse_excluir_arquivo_recusado($pdo, $courseId) : ['excluido' => false, 'aviso' => null];

mse_json([
    'ok' => true,
    'status' => $acao === 'aprovar' ? 'aprovado' : 'recusado',
    'arquivo_excluido' => $arquivo['excluido'],
    'aviso_arquivo' => $arquivo['aviso'],
]);
