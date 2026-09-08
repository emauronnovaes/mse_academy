<?php
declare(strict_types=1);

/**
 * Configura CORS. Defina ACADEMY_ALLOWED_ORIGIN no .env com a URL EXATA
 * do front-end (ex: https://portal.mseengenharia.com.br).
 *
 * Nunca use "*" aqui — como a API usa login (Authorization: Bearer),
 * liberar qualquer origem permitiria que outro site fizesse chamadas
 * autenticadas em nome de um usuário logado.
 */
function mse_cors(): void
{
    $allowedOrigin = getenv('ACADEMY_ALLOWED_ORIGIN') ?: '';
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($allowedOrigin !== '' && hash_equals($allowedOrigin, $origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
