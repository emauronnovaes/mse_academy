<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
mse_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

/**
 * Tira (ou devolve) uma pessoa das listas de acompanhamento.
 *
 * É só visual: a conta continua ativa, a pessoa continua acessando a
 * Academy e o progresso dela continua sendo registrado. Nada é apagado —
 * desmarcar faz ela voltar a aparecer com todo o histórico.
 *
 * Serve pra limpar das listas quem não é público do treinamento: contas
 * de teste, pessoal de TI, os próprios admins.
 */

$pdo = mse_db();

if ($pdo->query("SHOW COLUMNS FROM users LIKE 'oculto_em_relatorios'")->fetch() === false) {
    mse_error('Este servidor ainda não tem a coluna de ocultar. Falta rodar a migração 018 no banco (migrations/018_ocultar_de_relatorios.sql).', 409);
}

$input = mse_input();
$userId = (int) ($input['user_id'] ?? 0);
if ($userId <= 0) {
    mse_error('Informe user_id.', 422);
}

$stmt = $pdo->prepare('SELECT id, name, oculto_em_relatorios FROM users WHERE id = ?');
$stmt->execute([$userId]);
$pessoa = $stmt->fetch();
if (!$pessoa) {
    mse_error('Pessoa não encontrada.', 404);
}

// Sem "oculto" explícito, alterna — o botão da tela é um liga/desliga.
$oculto = isset($input['oculto'])
    ? (int) (bool) $input['oculto']
    : ((int) $pessoa['oculto_em_relatorios'] === 1 ? 0 : 1);

$stmt = $pdo->prepare('UPDATE users SET oculto_em_relatorios = ? WHERE id = ?');
$stmt->execute([$oculto, $userId]);

mse_json([
    'user_id' => $userId,
    'nome' => $pessoa['name'],
    'oculto' => $oculto === 1,
    'message' => $oculto === 1
        ? $pessoa['name'] . ' não aparece mais nas listas. A conta dela continua normal.'
        : $pessoa['name'] . ' voltou a aparecer nas listas.',
]);
