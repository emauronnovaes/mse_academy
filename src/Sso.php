<?php
declare(strict_types=1);

/**
 * Validação do token curto emitido pelo Portal MSE.
 *
 * Formato:
 *   base64url(payload_json).base64url(hmac_sha256(secret, base64url(payload_json)))
 *
 * O segredo deve ser igual ao SUPER_APP_SSO_SECRET do Portal. O fallback
 * preserva o valor padrão do helper inc/super_app_sso.php.
 */
function mse_portal_sso_secret(): string
{
    $secret = trim((string) getenv('SUPER_APP_SSO_SECRET'));
    if ($secret !== '') {
        return $secret;
    }

    return 'k9PqV2mX7zR4tLbN8sY6cFjA3wHdE5uGpT1oZxM0nQrWvB6iCsK2aDfL9yJhUeP';
}

function mse_base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/** @return string|false */
function mse_base64url_decode(string $data)
{
    if ($data === '' || preg_match('/^[A-Za-z0-9_-]+$/', $data) !== 1) {
        return false;
    }

    $remainder = strlen($data) % 4;
    if ($remainder === 1) {
        return false;
    }

    if ($remainder > 0) {
        $data .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(strtr($data, '-_', '+/'), true);
}

/**
 * Consome o nonce de forma atômica. Um token aceito não pode ser reutilizado,
 * nem por outra sessão, durante sua janela curta de validade.
 */
function mse_consume_sso_nonce(string $nonce, int $expiresAt): bool
{
    $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'mse-academy-sso-nonces';

    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        error_log('[mse_verify_sso_token] Não foi possível criar armazenamento de nonces.');
        return false;
    }

    $path = $directory . DIRECTORY_SEPARATOR . hash('sha256', $nonce);
    $handle = @fopen($path, 'x');
    if ($handle === false) {
        return false;
    }

    @fwrite($handle, (string) $expiresAt);
    @fclose($handle);
    @chmod($path, 0600);

    // Limpeza probabilística evita varredura em toda autenticação.
    if (random_int(1, 100) === 1) {
        $now = time();
        foreach ((array) @glob($directory . DIRECTORY_SEPARATOR . '*') as $candidate) {
            $storedExpiry = (int) @file_get_contents($candidate);
            if ($storedExpiry <= $now) {
                @unlink($candidate);
            }
        }
    }

    return true;
}

/**
 * @return array<string,mixed>|null
 */
function mse_verify_sso_token(string $token): ?array
{
    $GLOBALS['mse_sso_ultimo_erro'] = null;

    $secret = mse_portal_sso_secret();
    if ($secret === '') {
        $GLOBALS['mse_sso_ultimo_erro'] = 'segredo SSO não configurado';
        error_log('[mse_verify_sso_token] Segredo SSO vazio.');
        return null;
    }

    if ($token === '' || strlen($token) > 4096) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'tamanho do token inválido';
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        $GLOBALS['mse_sso_ultimo_erro'] = 'formato do token inválido';
        return null;
    }

    [$payloadB64, $signatureB64] = $parts;
    $expectedSignature = mse_base64url_encode(
        hash_hmac('sha256', $payloadB64, $secret, true)
    );

    if (!hash_equals($expectedSignature, $signatureB64)) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'assinatura inválida';
        error_log('[mse_verify_sso_token] Assinatura inválida.');
        return null;
    }

    $payloadJson = mse_base64url_decode($payloadB64);
    if ($payloadJson === false) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'payload Base64URL inválido';
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'payload JSON inválido';
        return null;
    }

    $identifier = trim((string) ($payload['email'] ?? ''));
    $cpf = preg_replace('/\D+/', '', $identifier);
    $validEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
    if (!$validEmail && strlen((string) $cpf) !== 11) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'identificador ausente ou inválido';
        return null;
    }

    $issuedAt = filter_var($payload['iat'] ?? null, FILTER_VALIDATE_INT);
    $expiresAt = filter_var($payload['exp'] ?? null, FILTER_VALIDATE_INT);
    $now = time();
    if (
        $issuedAt === false
        || $expiresAt === false
        || $expiresAt <= $now
        || $issuedAt > $now + 30
        || $expiresAt <= $issuedAt
        || ($expiresAt - $issuedAt) > 300
    ) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'janela temporal inválida ou expirada';
        return null;
    }

    $nonce = trim((string) ($payload['nonce'] ?? ''));
    if (preg_match('/^[a-f0-9]{16,128}$/i', $nonce) !== 1) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'nonce ausente ou inválido';
        return null;
    }

    if (!mse_consume_sso_nonce($nonce, (int) $expiresAt)) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'token já utilizado';
        error_log('[mse_verify_sso_token] Reutilização de token bloqueada.');
        return null;
    }

    return $payload;
}
