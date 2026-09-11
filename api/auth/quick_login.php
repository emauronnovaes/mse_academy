<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/PortalFichaApi.php';

/**
 * Login simplificado — a Academy CONFIA no e-mail que vier na URL,
 * sem exigir um token assinado (diferente de api/auth/sso.php).
 *
 * ⚠️ RISCO DE SEGURANÇA ACEITO DE PROPÓSITO (decisão tomada com o
 * cliente): sem assinatura, qualquer um que soubesse o e-mail de um
 * colega poderia digitar ele na URL e entrar "como" essa pessoa. Isso
 * só é aceitável porque o link "MSE Academy" fica DENTRO do Portal
 * (atrás do login dele) — quem não tem acesso ao Portal não teria como
 * chegar nesse link de qualquer jeito. Se algum dia quiser fechar essa
 * brecha, o caminho mais seguro é o token assinado (api/auth/sso.php,
 * já pronto e testado — só falta o Portal gerar o token no lado dele).
 *
 * Uso: GET /api/auth/quick_login.php?email=fulano@mse.com.br&nome=Fulano+de+Tal
 *   - email: obrigatório, é o identificador confiável.
 *   - nome: opcional — se vier, é usado pra buscar a ficha funcional
 *     (cargo oficial do RH) na API do Hub MSE. Sem nome, não dá pra
 *     buscar a ficha (a API não busca por e-mail).
 */

mse_cors();

$email = strtolower(trim((string) ($_GET['email'] ?? '')));
$nome = trim((string) ($_GET['nome'] ?? ''));

if ($email === '' || !mse_str_contains($email, '@')) {
    mse_error('Informe um e-mail válido (?email=fulano@mse.com.br).', 422);
}

if ($nome === '') {
    $nome = $email; // sem nome nenhum, usa o e-mail mesmo como "nome" (melhor que ficar vazio)
}

$cpf = null;
$cargo = null;

// Enriquecimento OPCIONAL com a ficha funcional (mesma lógica do
// sso.php) — só funciona se tivermos um nome pra buscar.
if ($nome !== $email) {
    $ficha = mse_portal_ficha_buscar($nome);
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

// Mesmo upsert atômico do sso.php (evita corrida se a pessoa clicar
// duas vezes rápido, ou abrir em duas abas ao mesmo tempo).
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
