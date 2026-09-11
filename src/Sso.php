<?php
declare(strict_types=1);

/**
 * Login via SSO do Portal MSE.
 *
 * A pessoa já está logada no Portal. Quando ela clica em "MSE Academy",
 * o PRÓPRIO PORTAL gera um token assinado (HMAC-SHA256) com os dados
 * dela e manda na URL, tipo:
 *
 *   https://academy.mse.com.br/?sso=eyJlbWFpbCI6Li4u.9f8a3b...
 *
 * A Academy só confere a assinatura com uma chave secreta que os dois
 * lados combinam (PORTAL_SSO_SECRET no .env) — não precisa chamar o
 * Portal de volta, e o Portal não precisa expor endpoint nenhum.
 *
 * Formato do token (antes de assinar): payload em JSON, base64url.
 *   { "email": "...", "cpf": "...", "nome": "...", "cargo": "...",
 *     "area_slug": "financeiro", "exp": 1723649000 }
 *
 * Token final = base64url(payload) + "." + hash_hmac('sha256', payload, SECRET)
 *
 * "exp" (expiração unix) deve ser BEM curto (60–300 segundos) — o token
 * só serve pra abrir a sessão uma vez, não fica reutilizável depois.
 */

function mse_base64url_decode(string $data)
{
    $padded = str_pad(strtr($data, '-_', '+/'), strlen($data) % 4 === 0 ? strlen($data) : strlen($data) + (4 - strlen($data) % 4), '=');
    return base64_decode($padded, true);
}

/**
 * Valida o token do Portal. Devolve o payload decodificado ou null se
 * inválido/expirado/assinatura errada.
 */
function mse_verify_sso_token(string $token): ?array
{
    $secret = getenv('PORTAL_SSO_SECRET') ?: '';
    if ($secret === '') {
        return null; // sem segredo configurado, recusa tudo (fail-safe)
    }

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
        return null;
    }
    [$payloadB64, $signature] = $parts;

    $expectedSignature = hash_hmac('sha256', $payloadB64, $secret);
    if (!hash_equals($expectedSignature, $signature)) {
        return null; // assinatura não bate — token forjado ou secret errado
    }

    $payloadJson = mse_base64url_decode($payloadB64);
    if ($payloadJson === false) {
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload) || empty($payload['email'])) {
        return null;
    }

    if (empty($payload['exp']) || (int) $payload['exp'] < time()) {
        return null; // expirado
    }

    return $payload;
}
