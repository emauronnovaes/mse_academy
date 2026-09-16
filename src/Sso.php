<?php
declare(strict_types=1);

/**
 * Validação do token curto emitido pelo Portal MSE.
 *
 * A pessoa já está logada no Portal. Quando ela clica em "MSE Academy",
 * o PRÓPRIO PORTAL gera um token assinado (HMAC-SHA256) com os dados
 * dela e manda na URL.
 *
 * Formato:
 *   base64url(payload_json).base64url(hmac_sha256(secret, base64url(payload_json)))
 *
 * A Academy só confere a assinatura com uma chave secreta que os dois
 * lados combinam (mse_portal_sso_secret(), logo abaixo) — não precisa
 * chamar o Portal de volta, e o Portal não precisa expor endpoint nenhum.
 *
 * O segredo deve ser IGUAL ao SUPER_APP_SSO_SECRET do Portal.
 */

/**
 * Chave secreta compartilhada com o Portal, usada pra assinar/validar o
 * token do SSO. Fica FIXA aqui no código (não no .env) — mesma lógica
 * de src/Cors.php: nem sempre quem sobe código pelo Git tem acesso pra
 * editar o .env no servidor. Se a chave mudar, só editar aqui e subir
 * pelo Git — os dois lados (Portal e Academy) precisam usar a MESMA
 * chave, combinada com quem administra o Portal.
 */
function mse_portal_sso_secret(): string
{
    return '32e44ea1f727606af8e9c91fe930cb829bdd4d4ee8702765e3832bcda08eaa49';
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

    // Limpeza oportunista de nonces velhos, sem precisar de um cron
    // separado — cada validação tem uma chance pequena de limpar o que
    // já expirou.
    if (mt_rand(1, 20) === 1) {
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.nonce') ?: [] as $file) {
            $expiry = (int) @file_get_contents($file);
            if ($expiry > 0 && $expiry < time()) {
                @unlink($file);
            }
        }
    }

    $path = $directory . DIRECTORY_SEPARATOR . $nonce . '.nonce';
    $handle = @fopen($path, 'x');
    if ($handle === false) {
        return false; // arquivo já existe — nonce já foi usado
    }
    fwrite($handle, (string) $expiresAt);
    fclose($handle);
    return true;
}

/**
 * Valida o token do Portal. Devolve o payload decodificado ou null se
 * inválido/expirado/assinatura errada/reutilizado.
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

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        $GLOBALS['mse_sso_ultimo_erro'] = 'formato do token inválido';
        return null;
    }
    [$payloadB64, $signatureB64] = $parts;

    $expectedSignature = hash_hmac('sha256', $payloadB64, $secret);
    if (!hash_equals($expectedSignature, $signatureB64)) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'Assinatura não bate — a chave PORTAL_SSO_SECRET usada pelo Portal pra assinar é diferente da configurada aqui na Academy (ou o payload foi montado de outro jeito)';
        error_log(sprintf(
            '[mse_verify_sso_token] Assinatura não bate. Esperada: %s... | Recebida: %s...',
            substr($expectedSignature, 0, 12),
            substr($signatureB64, 0, 12)
        ));
        return null;
    }

    $payloadJson = mse_base64url_decode($payloadB64);
    if ($payloadJson === false) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'payload não é base64url válido';
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'payload JSON inválido';
        return null;
    }

    // O campo "email" do payload nem sempre é um e-mail de verdade — o
    // Portal às vezes manda o CPF ali. Aceita os dois formatos.
    $identifier = trim((string) ($payload['email'] ?? ''));
    $cpf = preg_replace('/\D+/', '', $identifier);
    $validEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
    if (!$validEmail && strlen((string) $cpf) !== 11) {
        $GLOBALS['mse_sso_ultimo_erro'] = 'identificador ausente ou inválido';
        return null;
    }

    // Janela de tempo: iat (emitido em) + exp (expira em), com tolerância
    // pequena de relógio (30s) e validade máxima de 300s no total.
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
