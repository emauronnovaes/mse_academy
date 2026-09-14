<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/PortalFichaApi.php';
require_once __DIR__ . '/../../src/PortalSession.php';

/**
 * Login automático, lendo a sessão do Portal direto (sem precisar de
 * nada na URL) — só funciona se a Academy estiver hospedada no MESMO
 * domínio do Portal. Ver o aviso completo em src/PortalSession.php.
 *
 * O front-end chama isso PRIMEIRO, silenciosamente, assim que a página
 * carrega. Se não achar nada (404), cai pros métodos antigos
 * (?sso=... ou ?email=...) automaticamente — nenhum quebra o outro.
 */

mse_cors();

$sessao = mse_tentar_sessao_portal();

if ($sessao === null) {
    mse_error(
        'Não encontrei uma sessão do Portal ativa (ou o formato da sessão é diferente do esperado). ' .
        'Use ?email=... na URL como alternativa, ou veja scripts/diagnostico_sessao_portal.php.',
        404
    );
}

$email = $sessao['email'];
$nome = $sessao['nome'] ?: $email;

$cpf = null;
$cargo = null;

if ($sessao['nome']) {
    $ficha = null;
    try {
        $ficha = mse_portal_ficha_buscar($sessao['nome']);
    } catch (Throwable $e) {
        error_log('[portal_session.php] Enriquecimento via ficha falhou (ignorado): ' . $e->getMessage());
    }
    if ($ficha !== null) {
        if (!empty($ficha['funcao'])) {
            $cargo = $ficha['funcao'];
        }
        if (!empty($ficha['cpf'])) {
            $cpf = $ficha['cpf'];
        }
    }
}

$primeiroNome = mse_first_name_from_email($email);

$pdo = mse_db();

$stmt = $pdo->prepare(
    "INSERT INTO users (name, first_name, email, cpf, cargo, role, provisioned_via)
     VALUES (?, ?, ?, ?, ?, 'colaborador', 'sso')
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       first_name = VALUES(first_name),
       cpf = COALESCE(VALUES(cpf), cpf),
       cargo = COALESCE(VALUES(cargo), cargo),
       active = 1,
       id = LAST_INSERT_ID(id)"
);
$stmt->execute([$nome, $primeiroNome, $email, $cpf, $cargo]);
$userId = (int) $pdo->lastInsertId();
$isFirstLogin = $stmt->rowCount() === 1;

$access = mse_track_access($userId);
$sessionToken = mse_create_session($userId);

mse_json([
    'token' => $sessionToken,
    'user' => [
        'id' => $userId,
        'name' => $nome,
        'first_name' => $primeiroNome,
        'email' => $email,
        'cargo' => $cargo,
    ],
    'access' => [
        'is_first_login' => $isFirstLogin,
        'distinct_access_count' => $access['distinct_access_count'],
    ],
]);
