<?php
declare(strict_types=1);

/**
 * Tenta puxar o login direto da sessão PHP do Portal — só funciona se
 * a Academy estiver no MESMO domínio do Portal (cookie de sessão é
 * compartilhado automaticamente pelo navegador nesse caso, sem
 * precisar de link especial nem nada digitado na URL).
 *
 * Como eu não sei o nome EXATO da variável que o Portal usa pra guardar
 * o login (pode ser $_SESSION['email'], $_SESSION['usuario']['email'],
 * etc — cada sistema guarda de um jeito), tento os formatos mais comuns
 * em sequência. Se nenhum bater, retorna null (a Academy cai pro método
 * antigo, de ?email= na URL, que sempre funciona como reserva).
 *
 * ⚠️ Se isso não achar o login de verdade, significa que o formato real
 * da sessão do Portal é diferente dos que tentei aqui — nesse caso,
 * alguém com acesso ao Portal precisa me dizer o nome exato da
 * variável (ver scripts/diagnostico_sessao_portal.php).
 */
function mse_tentar_sessao_portal(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    if (empty($_SESSION)) {
        return null; // sessão vazia — ou a pessoa não está logada, ou é outro domínio
    }

    // Tentativas de onde o e-mail pode estar guardado, dos formatos
    // mais comuns pros menos comuns.
    $tentativasEmail = [
        $_SESSION['email'] ?? null,
        $_SESSION['user_email'] ?? null,
        $_SESSION['usuario']['email'] ?? null,
        $_SESSION['user']['email'] ?? null,
        $_SESSION['dados']['email'] ?? null,
        $_SESSION['funcionario']['email'] ?? null,
        $_SESSION['auth']['email'] ?? null,
    ];

    $email = null;
    foreach ($tentativasEmail as $tentativa) {
        if (!empty($tentativa) && is_string($tentativa) && mse_str_contains($tentativa, '@')) {
            $email = strtolower(trim($tentativa));
            break;
        }
    }

    if ($email === null) {
        return null; // nenhum formato bateu — precisa descobrir o nome real da variável
    }

    // Mesma lógica pro nome, mas o nome é opcional (o e-mail sozinho já
    // basta pra identificar a pessoa).
    $tentativasNome = [
        $_SESSION['nome'] ?? null,
        $_SESSION['name'] ?? null,
        $_SESSION['usuario']['nome'] ?? null,
        $_SESSION['user']['name'] ?? null,
        $_SESSION['dados']['nome'] ?? null,
        $_SESSION['funcionario']['nome'] ?? null,
    ];
    $nome = null;
    foreach ($tentativasNome as $tentativa) {
        if (!empty($tentativa) && is_string($tentativa)) {
            $nome = trim($tentativa);
            break;
        }
    }

    return ['email' => $email, 'nome' => $nome];
}
