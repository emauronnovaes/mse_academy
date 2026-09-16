<?php
declare(strict_types=1);

/**
 * SÓ PRA DESENVOLVIMENTO LOCAL — gera um link de login de teste, do jeito
 * que o Portal MSE geraria de verdade em produção. Sem isso, não tem
 * como testar o SSO no seu computador (o Portal real não existe aqui).
 *
 * NUNCA rode isso em produção — lá o token vem do Portal de verdade.
 *
 * Como usar:
 *   php scripts/generate_test_login.php matheus.batista@mse.com.br
 *   php scripts/generate_test_login.php outro@mse.com.br "Nome da Pessoa"
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Sso.php';

$email = $argv[1] ?? null;
$nome = $argv[2] ?? null;

if (!$email) {
    echo "Uso: php scripts/generate_test_login.php email@mse.com.br \"Nome (opcional)\"\n";
    exit(1);
}

$secret = mse_portal_sso_secret();
if ($secret === '') {
    echo "ERRO: a chave em mse_portal_sso_secret() (src/Sso.php) está vazia\n";
    exit(1);
}

$now = time();
$payload = json_encode([
    'email' => $email,
    'nome' => $nome ?: $email,
    'iat' => $now,
    'exp' => $now + 120, // 2 minutos — dentro do limite de 300s exigido
    'nonce' => bin2hex(random_bytes(16)),
]);
$payloadB64 = mse_base64url_encode($payload);
$signature = mse_base64url_encode(hash_hmac('sha256', $payloadB64, $secret, true));
$token = $payloadB64 . '.' . $signature;

echo "Token gerado (válido por 2 minutos, uso único):\n\n";
echo "http://localhost/?sso={$token}\n\n";
echo "Abre esse link no navegador (com o site rodando) pra entrar como {$email}.\n";
