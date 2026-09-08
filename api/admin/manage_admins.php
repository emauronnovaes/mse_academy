<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();
$admin = mse_require_admin(); // só admin existente pode promover outro

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$email = strtolower(trim((string) ($input['email'] ?? '')));
$action = (string) ($input['action'] ?? ''); // 'promote' ou 'demote'

if ($email === '' || !str_contains($email, '@')) {
    mse_error('Informe um e-mail válido.', 422);
}
if (!in_array($action, ['promote', 'demote'], true)) {
    mse_error('action precisa ser "promote" ou "demote".', 422);
}
if ($action === 'demote' && $email === $admin['email']) {
    mse_error('Você não pode remover o seu próprio acesso de admin (peça pra outro admin fazer isso).', 422);
}

$pdo = mse_db();

if ($action === 'promote') {
    // Mesmo truque da migração: funciona pra e-mail que já tem conta OU
    // que ainda nunca acessou a Academy — nesse caso fica com um nome
    // provisório até a pessoa entrar de verdade pelo SSO pela 1ª vez
    // (o UPDATE do sso.php nunca sobrescreve a coluna "role").
    $placeholderName = mse_first_name_from_email($email) . ' (aguardando primeiro acesso)';
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, first_name, email, role, provisioned_via)
         VALUES (?, ?, ?, 'admin', 'sso')
         ON DUPLICATE KEY UPDATE role = 'admin'"
    );
    $stmt->execute([$placeholderName, mse_first_name_from_email($email), $email]);

    mse_json(['email' => $email, 'role' => 'admin', 'message' => "{$email} agora é admin."]);
} else {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        mse_error("{$email} não tem conta na Academy ainda (nunca acessou), não tem o que remover.", 404);
    }

    $stmt = $pdo->prepare("UPDATE users SET role = 'colaborador' WHERE email = ?");
    $stmt->execute([$email]);

    mse_json(['email' => $email, 'role' => 'colaborador', 'message' => "{$email} não é mais admin."]);
}
