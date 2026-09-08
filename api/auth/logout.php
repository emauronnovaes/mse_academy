<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

mse_cors();

$header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
if (preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
    $hash = mse_hash_token($matches[1]);
    $pdo = mse_db();
    $stmt = $pdo->prepare('DELETE FROM auth_tokens WHERE token_hash = ?');
    $stmt->execute([$hash]);
}

mse_json(['ok' => true]);
