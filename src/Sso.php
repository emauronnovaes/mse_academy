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
    $GLOBALS['mse_sso_ultimo_erro'] = null; // reseta a cada chamada

    $secret = getenv('PORTAL_SSO_SECRET') ?: '';
    if ($secret === '') {
        $GLOBALS['mse_sso_ultimo_erro'] = 'PORTAL_SSO_SECRET não está configurado no servidor da Academy (.env)';
        error_log('[mse_verify_sso_token] PORTAL_SSO_SECRET não configurado no .env — recusando tudo.');
        return null; // sem segredo configurado, recusa tudo (fail-safe)
    }

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'Formato do token inválido (esperado "payload.assinatura", faltou o ponto separador)';
        error_log('[mse_verify_sso_token] Token não tem o formato "payload.assinatura" esperado (não achei o ponto separador).');
        return null;
    }
    [$payloadB64, $signature] = $parts;

    $expectedSignature = hash_hmac('sha256', $payloadB64, $secret);
    if (!hash_equals($expectedSignature, $signature)) {
        // Log detalhado (só os primeiros caracteres da assinatura, não
        // o token inteiro) — ajuda a confirmar se é problema de chave
        // secreta diferente dos dois lados, ou de formato do payload.
        $GLOBALS['mse_sso_ultimo_erro'] = 'Assinatura não bate — a chave PORTAL_SSO_SECRET usada pelo Portal pra assinar é diferente da configurada aqui na Academy (ou o payload foi montado de outro jeito)';
        error_log(sprintf(
            '[mse_verify_sso_token] Assinatura não bate. Esperada (calculada aqui): %s... | Recebida (do token): %s... | Tamanho do payload recebido: %d chars.',
            substr($expectedSignature, 0, 12),
            substr($signature, 0, 12),
            strlen($payloadB64)
        ));
        return null; // assinatura não bate — token forjado ou secret errado
    }

    $payloadJson = mse_base64url_decode($payloadB64);
    if ($payloadJson === false) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'Assinatura bateu, mas o payload não é base64 válido';
        error_log('[mse_verify_sso_token] A assinatura bateu, mas não consegui decodificar o payload como base64 válido.');
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload) || empty($payload['email'])) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'Payload decodificado não é um JSON válido, ou não tem o campo "email"';
        error_log('[mse_verify_sso_token] Payload decodificado não é um JSON válido, ou não tem o campo "email". Conteúdo decodificado: ' . substr((string) $payloadJson, 0, 200));
        return null;
    }

    if (empty($payload['exp']) || (int) $payload['exp'] < time()) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'Token expirado (campo "exp" no passado, ou ausente)';
        error_log(sprintf(
            '[mse_verify_sso_token] Token expirado (ou sem "exp"). exp=%s, agora=%d, diferença=%d segundos.',
            $payload['exp'] ?? '(ausente)',
            time(),
            time() - (int) ($payload['exp'] ?? 0)
        ));
        return null; // expirado
    }

    return $payload;
}
