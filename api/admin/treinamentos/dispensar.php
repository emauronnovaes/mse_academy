<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Admitidos.php';

/**
 * Dispensa (ou reativa) um admitido da integração.
 *
 * Dispensado sai da lista de Pendentes e da view v_integracao_pendentes,
 * mas nada é apagado: "reativar" desfaz. Guarda quem dispensou, quando e
 * o motivo.
 *
 * POST {nome, cpf?, data_admissao, acao: 'dispensar' | 'reativar', motivo?}
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$in = mse_input();
$acao = (string) ($in['acao'] ?? '');
$nome = mb_substr(trim((string) ($in['nome'] ?? '')), 0, 200);
$cpf = preg_replace('/\D/', '', (string) ($in['cpf'] ?? ''));
$data = (string) ($in['data_admissao'] ?? '');
$motivo = mb_substr(trim((string) ($in['motivo'] ?? '')), 0, 300);
if (!in_array($acao, ['dispensar', 'reativar'], true) || $nome === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    mse_error('Informe nome, data_admissao e acao ("dispensar" ou "reativar").', 422);
}

$pdo = mse_db();
[$chaveCpf, $chaveNome] = mse_integracao_chaves($cpf ?: null, $nome, $data);

if ($acao === 'dispensar') {
    if (!mse_garantir_tabela_dispensados($pdo)) {
        mse_error('Não consegui criar a tabela de dispensados. Rode a migração 028 no banco (migrations/028_integracao_dispensados.sql).', 409);
    }
    $agora = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
    $pdo->prepare(
        'INSERT INTO integracao_dispensados (chave, nome, cpf, data_admissao, motivo, dispensado_por, dispensado_em)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE motivo = VALUES(motivo), dispensado_por = VALUES(dispensado_por), dispensado_em = VALUES(dispensado_em)'
    )->execute([$chaveCpf, $nome, $cpf ?: null, $data, $motivo ?: null, (string) $admin['name'], $agora]);
    mse_json(['ok' => true, 'dispensa' => ['motivo' => $motivo ?: null, 'por' => (string) $admin['name'], 'em' => $agora]]);
}

// reativar: tira o registro de dispensa (por CPF ou por nome+data)
$st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
$st->execute(['integracao_dispensados']);
if ($st->fetchColumn() !== false) {
    $pdo->prepare('DELETE FROM integracao_dispensados WHERE chave IN (?, ?)')->execute([$chaveCpf, $chaveNome]);
}
mse_json(['ok' => true]);
