<?php
declare(strict_types=1);

/**
 * Envio de e-mail pela Academy, sem biblioteca externa.
 *
 * Usa o SMTP configurado no .env do servidor:
 *   SMTP_HOST       ex.: smtp.office365.com
 *   SMTP_PORT       587 (STARTTLS, padrão) ou 465 (SSL)
 *   SMTP_USER       conta que envia
 *   SMTP_PASS       senha (ou senha de app) dessa conta
 *   SMTP_SECURE     tls | ssl | none (opcional — deduzido da porta)
 *   MAIL_FROM       remetente (padrão: SMTP_USER)
 *   MAIL_FROM_NAME  nome do remetente (padrão: MSE Academy)
 *
 * Sem SMTP_HOST, mas com MAIL_FROM, tenta o mail() do PHP (sendmail do
 * servidor). Sem nenhum dos dois, não envia e avisa — quem chamou segue
 * normalmente, o e-mail é só um aviso.
 */

function mse_email_configurado(): bool
{
    return trim(mse_env('SMTP_HOST')) !== '' || trim(mse_env('MAIL_FROM')) !== '';
}

/**
 * Envia uma mensagem (HTML + texto puro) para uma lista de endereços.
 * Devolve ['ok' => bool, 'erro' => ?string]. Nunca lança exceção.
 */
function mse_enviar_email(array $para, string $assunto, string $html, string $texto): array
{
    $para = array_values(array_unique(array_filter(array_map('trim', $para), static function ($e) {
        return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
    })));
    if (!$para) {
        return ['ok' => false, 'erro' => 'Nenhum destinatário com e-mail válido.'];
    }
    if (!mse_email_configurado()) {
        return ['ok' => false, 'erro' => 'E-mail não configurado no servidor (SMTP_HOST no .env).'];
    }

    $de = trim(mse_env('MAIL_FROM')) ?: trim(mse_env('SMTP_USER'));
    $nomeDe = trim(mse_env('MAIL_FROM_NAME')) ?: 'MSE Academy';
    $mensagem = mse_email_montar($de, $nomeDe, $para, $assunto, $html, $texto);

    try {
        if (trim(mse_env('SMTP_HOST')) !== '') {
            mse_smtp_enviar($de, $para, $mensagem);
        } else {
            [$cabecalhos, $corpo] = explode("\r\n\r\n", $mensagem, 2);
            // mail() recebe To e Subject à parte: tira dos cabeçalhos pra não duplicar.
            $cabecalhos = preg_replace('/^(To|Subject): .*(\r\n[ \t].*)*\r\n/mi', '', $cabecalhos . "\r\n");
            if (!mail(implode(', ', $para), mse_email_cabecalho_utf8($assunto), $corpo, rtrim($cabecalhos))) {
                throw new RuntimeException('mail() do servidor recusou o envio.');
            }
        }
        return ['ok' => true, 'erro' => null];
    } catch (Throwable $e) {
        error_log('[email] ' . $e->getMessage());
        return ['ok' => false, 'erro' => $e->getMessage()];
    }
}

function mse_email_cabecalho_utf8(string $texto): string
{
    return preg_match('/[^\x20-\x7E]/', $texto) ? '=?UTF-8?B?' . base64_encode($texto) . '?=' : $texto;
}

function mse_email_montar(string $de, string $nomeDe, array $para, string $assunto, string $html, string $texto): string
{
    $limite = 'mse-' . bin2hex(random_bytes(12));
    $dominio = substr(strrchr($de, '@') ?: '@academy.local', 1);
    $cabecalhos = [
        'Date: ' . date('r'),
        'From: ' . mse_email_cabecalho_utf8($nomeDe) . " <{$de}>",
        'To: ' . implode(', ', $para),
        'Subject: ' . mse_email_cabecalho_utf8($assunto),
        'Message-ID: <' . bin2hex(random_bytes(16)) . "@{$dominio}>",
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"{$limite}\"",
    ];
    $parte = static function (string $tipo, string $conteudo) use ($limite): string {
        return "--{$limite}\r\nContent-Type: {$tipo}; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . rtrim(chunk_split(base64_encode($conteudo), 76, "\r\n")) . "\r\n";
    };
    return implode("\r\n", $cabecalhos) . "\r\n\r\n"
        . $parte('text/plain', $texto)
        . $parte('text/html', $html)
        . "--{$limite}--\r\n";
}

/** Conversa SMTP mínima: EHLO, STARTTLS, AUTH LOGIN, MAIL/RCPT/DATA. */
function mse_smtp_enviar(string $de, array $para, string $mensagem): void
{
    $host = trim(mse_env('SMTP_HOST'));
    $porta = (int) (mse_env('SMTP_PORT') ?: '587');
    $seguranca = strtolower(trim(mse_env('SMTP_SECURE')));
    if ($seguranca === '') {
        $seguranca = $porta === 465 ? 'ssl' : ($porta === 25 ? 'none' : 'tls');
    }

    $contexto = stream_context_create(['ssl' => ['peer_name' => $host, 'SNI_enabled' => true]]);
    $socket = @stream_socket_client(
        ($seguranca === 'ssl' ? 'ssl://' : 'tcp://') . "{$host}:{$porta}",
        $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $contexto
    );
    if (!$socket) {
        throw new RuntimeException("Não conectei no servidor de e-mail {$host}:{$porta} ({$errstr}).");
    }
    stream_set_timeout($socket, 30);

    $comando = static function (?string $linha, array $esperado, string $rotulo) use ($socket): string {
        if ($linha !== null) {
            fwrite($socket, $linha . "\r\n");
        }
        $resposta = '';
        while (($l = fgets($socket, 1024)) !== false) {
            $resposta .= $l;
            if (strlen($l) < 4 || $l[3] === ' ') {
                break;
            }
        }
        if (!in_array((int) substr($resposta, 0, 3), $esperado, true)) {
            // A resposta do servidor nunca contém a senha, só o código e o motivo.
            throw new RuntimeException("SMTP ({$rotulo}): " . trim($resposta ?: 'sem resposta'));
        }
        return $resposta;
    };

    try {
        $comando(null, [220], 'conexão');
        $ehlo = 'EHLO ' . (preg_replace('/[^A-Za-z0-9.-]/', '', (string) gethostname()) ?: 'localhost');
        $comando($ehlo, [250], 'EHLO');
        if ($seguranca === 'tls') {
            $comando('STARTTLS', [220], 'STARTTLS');
            $metodo = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : 0);
            if (!stream_socket_enable_crypto($socket, true, $metodo)) {
                throw new RuntimeException('SMTP: não consegui ativar a criptografia (STARTTLS).');
            }
            $comando($ehlo, [250], 'EHLO');
        }
        $usuario = mse_env('SMTP_USER');
        if ($usuario !== '') {
            $comando('AUTH LOGIN', [334], 'AUTH');
            $comando(base64_encode($usuario), [334], 'AUTH usuário');
            $comando(base64_encode(mse_env('SMTP_PASS')), [235], 'AUTH senha');
        }
        $comando("MAIL FROM:<{$de}>", [250], 'MAIL FROM');
        foreach ($para as $endereco) {
            $comando("RCPT TO:<{$endereco}>", [250, 251], 'RCPT TO');
        }
        $comando('DATA', [354], 'DATA');
        // Linha que começa com ponto ganha outro ponto (regra do SMTP).
        $corpo = preg_replace('/^\./m', '..', $mensagem);
        $comando($corpo . "\r\n.", [250], 'envio');
        @fwrite($socket, "QUIT\r\n"); // já entregue; a resposta do QUIT não importa
    } finally {
        fclose($socket);
    }
}
