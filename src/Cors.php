<?php
declare(strict_types=1);

/**
 * Lista de origens (protocolo + domínio + porta) de onde a Academy pode
 * ser acessada. Fica FIXA aqui no código (não no .env) — decisão tomada
 * porque nem sempre quem consegue subir uma atualização pelo Git tem
 * acesso pra editar o .env no servidor. Se o domínio mudar algum dia,
 * é só editar essa lista e subir pelo Git, sem mexer no servidor.
 *
 * SEM caminho e SEM barra no final (ex: "https://portalmse.com.br",
 * não "https://portalmse.com.br/academy/").
 */
function mse_origens_permitidas(): array
{
    return [
        'https://portalmse.com.br',
        'https://mseacademy.portalmse.com.br', // subdomínio visto no console do navegador
    ];
}

/**
 * Configura CORS.
 *
 * Nunca use "*" aqui — como a API usa login (Authorization: Bearer),
 * liberar qualquer origem permitiria que outro site fizesse chamadas
 * autenticadas em nome de um usuário logado.
 */
function mse_cors(): void
{
    $origensPermitidas = mse_origens_permitidas();
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin !== '' && in_array($origin, $origensPermitidas, true)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Vary: Origin');
    } elseif ($origin !== '') {
        // Fica registrado no log do servidor sempre que uma origem for
        // recusada — ajuda a diagnosticar sem precisar de nenhuma
        // ferramenta extra (ver também scripts/diagnostico_cors.php).
        error_log("[mse_cors] Origem recusada: recebido \"{$origin}\", permitidas: " . implode(', ', $origensPermitidas));
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
