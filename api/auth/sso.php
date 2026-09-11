<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Sso.php';
require_once __DIR__ . '/../../src/PortalFichaApi.php';

mse_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$token = (string) ($input['token'] ?? '');

if ($token === '') {
    mse_error('Token do Portal não informado.', 422);
}

$payload = mse_verify_sso_token($token);
if ($payload === null) {
    mse_error('Token do Portal inválido ou expirado. Volte ao Portal e clique em MSE Academy de novo.', 401);
}

$email = strtolower(trim((string) $payload['email']));
$cpf = isset($payload['cpf']) ? preg_replace('/\D/', '', (string) $payload['cpf']) : null;
$nome = (string) ($payload['nome'] ?? $email);
$cargo = isset($payload['cargo']) ? (string) $payload['cargo'] : null;
$areaSlug = isset($payload['area_slug']) ? (string) $payload['area_slug'] : null;

// Enriquecimento OPCIONAL com a ficha funcional oficial do RH (API
// ff_infos do Hub MSE) — busca pelo nome que já veio no token. Se a API
// falhar, der timeout, ou não achar ninguém, o login segue normalmente
// só com o que o token do Portal já trouxe — isso aqui nunca pode
// travar ninguém de entrar na Academy.
$ficha = mse_portal_ficha_buscar($nome);
if ($ficha !== null) {
    // Função/cargo oficial do RH é mais confiável que o que o token do
    // Portal eventualmente mande — priorizamos ela quando disponível
    // (isso melhora a recomendação de cursos por cargo).
    if (!empty($ficha['funcao'])) {
        $cargo = $ficha['funcao'];
    }
    // CPF: usa o da ficha só se o token não tiver mandado nenhum.
    if (empty($cpf) && !empty($ficha['cpf'])) {
        $cpf = $ficha['cpf'];
    }
    // E-mail: a documentação da API não lista esse campo, mas alguns
    // registros trazem — só usamos como ÚLTIMO recurso, se por algum
    // motivo o token do Portal não tiver mandado e-mail nenhum.
    if (empty($email) && !empty($ficha['email'])) {
        $email = $ficha['email'];
    }
}

// Caso extremo: mesmo depois do token do Portal E da ficha do RH,
// ninguém trouxe e-mail nenhum. A tabela "users" hoje exige e-mail
// único — usamos o CPF como identificador nesse caso raro, em vez de
// travar o login (mas isso é mesmo bem incomum: o Portal normalmente
// sempre manda e-mail no token).
if (empty($email)) {
    if (!empty($cpf)) {
        $email = $cpf . '@sem-email.mse.local';
    } else {
        mse_error('Não foi possível identificar a pessoa — nem e-mail nem CPF disponíveis.', 422);
    }
}

$primeiroNome = mse_first_name_from_email($email);

$pdo = mse_db();

$areaId = null;
if ($areaSlug) {
    $stmt = $pdo->prepare('SELECT id FROM areas WHERE slug = ?');
    $stmt->execute([$areaSlug]);
    $area = $stmt->fetch();
    $areaId = $area ? (int) $area['id'] : null;
    // Se o Portal mandar um slug de área que a Academy não conhece,
    // seguimos sem travar o login — só não conseguimos recomendar por área
    // até alguém corrigir o slug (ver tabela "areas").
}

// Upsert ATÔMICO: o próprio MySQL resolve "já existe ou não" de uma vez,
// sem janela de corrida. Antes fazíamos SELECT e decidíamos no PHP depois
// (INSERT ou UPDATE) — sob muita gente entrando ao mesmo tempo (ex: todo
// mundo clicando em "MSE Academy" logo depois de um comunicado), duas
// requisições da MESMA pessoa nova podiam cair exatamente juntas, as duas
// veem "não existe" e as duas tentam criar — a segunda quebra com erro de
// e-mail duplicado. Testado sob carga real: 10 requisições simultâneas
// pra um e-mail novo, 9 falhavam. Com ON DUPLICATE KEY UPDATE, as 10
// funcionam e todas resolvem pro mesmo usuário.
$stmt = $pdo->prepare(
    "INSERT INTO users (name, first_name, email, cpf, cargo, area_id, role, provisioned_via)
     VALUES (?, ?, ?, ?, ?, ?, 'colaborador', 'sso')
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       first_name = VALUES(first_name),
       cpf = COALESCE(VALUES(cpf), cpf),
       cargo = VALUES(cargo),
       area_id = VALUES(area_id),
       active = 1,
       id = LAST_INSERT_ID(id)"
);
$stmt->execute([$nome, $primeiroNome, $email, $cpf, $cargo, $areaId]);
$userId = (int) $pdo->lastInsertId();

// LAST_INSERT_ID(id) no UPDATE devolve o id da linha existente; se o
// INSERT "pegou" (linha nova de verdade), affected_rows vem 1. Se caiu no
// UPDATE, affected_rows vem 2 (comportamento padrão do MySQL pra upsert).
// Usamos isso só pra saber se é a primeira vez, sem precisar de outro SELECT.
$isFirstLogin = $stmt->rowCount() === 1;

// Marca o acesso de hoje (só conta 1 vez por dia, mesmo com vários logins)
$access = mse_track_access($userId);

$sessionToken = mse_create_session($userId);

mse_json([
    'token' => $sessionToken,
    'user' => [
        'id' => $userId,
        'name' => $nome,
        'first_name' => $primeiroNome, // usar esse no "Bem-vindo, {first_name}"
        'email' => $email,
        'cargo' => $cargo,
        'area_slug' => $areaSlug,
    ],
    'access' => [
        'is_first_login' => $isFirstLogin,       // true -> "Bem-vindo"; false -> "Bem-vindo de volta"
        'distinct_access_count' => $access['distinct_access_count'],
    ],
]);
