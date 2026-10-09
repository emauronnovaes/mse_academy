<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Viniconsultas.php';

mse_cors();

$header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
if (preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
    $hash = mse_hash_token($matches[1]);
    $pdo = mse_db();
    // Quem está saindo? (antes de apagar a sessão) — vai para o log.
    $quem = $pdo->prepare('SELECT user_id FROM auth_tokens WHERE token_hash = ? LIMIT 1');
    $quem->execute([$hash]);
    $uid = (int) $quem->fetchColumn();
    if ($uid > 0) {
        mse_vini_registrar_evento($uid, 'logout', ['origem' => 'academy']);
    }
    $stmt = $pdo->prepare('DELETE FROM auth_tokens WHERE token_hash = ?');
    $stmt->execute([$hash]);
}

mse_json(['ok' => true]);
