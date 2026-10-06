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

    // Cargo, CPF e setor: mesmos lugares de onde vêm e-mail e nome. Antes
    // não eram lidos, e o cargo dependia só da API de ficha.
    $cargo = mse_sessao_valor(['cargo', 'funcao', 'função', 'nome_cargo', 'cargo_nome', 'desc_cargo', 'descricao_cargo', 'nome_funcao']);
    $cpf = preg_replace('/\D+/', '', (string) mse_sessao_valor(['cpf', 'user_cpf', 'documento']));
    $setor = mse_sessao_valor(['setor', 'departamento', 'obras_departamento', 'area', 'nome_setor']);

    return [
        'email' => $email,
        'nome' => $nome,
        'cargo' => $cargo,
        'cpf' => strlen((string) $cpf) === 11 ? $cpf : null,
        'setor' => $setor,
    ];
}

/**
 * Primeiro valor de texto encontrado na sessão, procurando cada chave na
 * raiz e dentro dos agrupamentos mais comuns (usuario, user, dados...).
 */
function mse_sessao_valor(array $chaves): ?string
{
    $grupos = [$_SESSION];
    foreach (['usuario', 'user', 'dados', 'funcionario', 'colaborador', 'auth', 'login'] as $g) {
        if (isset($_SESSION[$g]) && is_array($_SESSION[$g])) {
            $grupos[] = $_SESSION[$g];
        }
    }
    foreach ($chaves as $chave) {
        foreach ($grupos as $grupo) {
            if (isset($grupo[$chave]) && is_scalar($grupo[$chave]) && trim((string) $grupo[$chave]) !== '') {
                return trim((string) $grupo[$chave]);
            }
        }
    }
    return null;
}

/**
 * Nomes das variáveis da sessão do Portal (só os nomes, nunca os valores),
 * pro diagnóstico mostrar onde o cargo está guardado.
 */
function mse_sessao_chaves(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $chaves = [];
    foreach ($_SESSION ?? [] as $k => $v) {
        if (is_array($v)) {
            foreach (array_keys($v) as $k2) {
                $chaves[] = $k . '.' . $k2;
            }
        } else {
            $chaves[] = (string) $k;
        }
    }
    return $chaves;
}
