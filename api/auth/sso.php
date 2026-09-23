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
    $reason = $GLOBALS['mse_sso_ultimo_erro'] ?? 'motivo desconhecido';
    mse_error(
        "Token do Portal inválido ou expirado ({$reason}). Volte ao Portal e abra a MSE Academy novamente.",
        401
    );
}

$identifier = strtolower(trim((string) $payload['email']));
$identifierCpf = preg_replace('/\D+/', '', $identifier);
$email = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false ? $identifier : '';

$payloadCpf = preg_replace('/\D+/', '', (string) ($payload['cpf'] ?? ''));
$cpf = strlen((string) $payloadCpf) === 11
    ? $payloadCpf
    : (strlen((string) $identifierCpf) === 11 ? $identifierCpf : null);

$nome = trim((string) ($payload['nome'] ?? ''));
if ($nome === '') {
    $nome = $email !== '' ? $email : (string) $cpf;
}

$cargo = isset($payload['cargo']) && trim((string) $payload['cargo']) !== ''
    ? trim((string) $payload['cargo'])
    : null;
$areaSlug = isset($payload['area_slug']) && trim((string) $payload['area_slug']) !== ''
    ? trim((string) $payload['area_slug'])
    : null;

// Enriquecimento opcional. Falhas externas nunca bloqueiam autenticação.
$ficha = null;
try {
    $ficha = mse_portal_ficha_buscar($nome);
} catch (Throwable $e) {
    error_log('[sso.php] Enriquecimento via ficha falhou (ignorado): ' . $e->getMessage());
}

if ($ficha !== null) {
    if (!empty($ficha['funcao'])) {
        $cargo = $ficha['funcao'];
    }
    if (empty($cpf) && !empty($ficha['cpf'])) {
        $fichaCpf = preg_replace('/\D+/', '', (string) $ficha['cpf']);
        if (strlen((string) $fichaCpf) === 11) {
            $cpf = $fichaCpf;
        }
    }
    if ($email === '' && !empty($ficha['email'])) {
        $fichaEmail = strtolower(trim((string) $ficha['email']));
        if (filter_var($fichaEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $email = $fichaEmail;
        }
    }
}

// A tabela usa e-mail como chave única. Para login por CPF, cria chave interna
// estável sem inventar um endereço externo.
if ($email === '') {
    if (!empty($cpf)) {
        $email = $cpf . '@sem-email.mse.local';
    } else {
        mse_error('Não foi possível identificar a pessoa.', 422);
    }
}

$primeiroNome = mse_first_name_from_email($email);
$pdo = mse_db();

$areaId = null;
if ($areaSlug !== null) {
    $stmt = $pdo->prepare('SELECT id FROM areas WHERE slug = ?');
    $stmt->execute([$areaSlug]);
    $area = $stmt->fetch();
    $areaId = $area ? (int) $area['id'] : null;
}

// Setor oficial do RH. Mesma lógica já usada pro cargo: o dado do RH
// manda mais que o do token, porque é onde a informação é mantida de
// verdade. Só substitui quando o setor casa com uma área cadastrada —
// um setor desconhecido não pode apagar a área que a pessoa já tinha.
if ($ficha !== null && !empty($ficha['obras_departamento'])) {
    $areaDoRh = mse_area_id_por_setor($pdo, $ficha['obras_departamento']);
    if ($areaDoRh !== null) {
        $areaId = $areaDoRh;
    }
}

// Última tentativa: deduzir pelo cargo. Só entra quando nem o RH nem o
// token informaram o setor — é o caso mais comum, já que o cargo chega
// com muito mais frequência que o departamento.
if ($areaId === null) {
    $areaId = mse_area_id_por_cargo($pdo, $cargo);
}

$stmt = $pdo->prepare(
    "INSERT INTO users (name, first_name, email, cpf, cargo, area_id, role, provisioned_via)
     VALUES (?, ?, ?, ?, ?, ?, 'colaborador', 'sso')
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       first_name = VALUES(first_name),
       cpf = COALESCE(VALUES(cpf), cpf),
       cargo = COALESCE(VALUES(cargo), cargo),
       area_id = COALESCE(VALUES(area_id), area_id),
       active = 1,
       id = LAST_INSERT_ID(id)"
);
$stmt->execute([$nome, $primeiroNome, $email, $cpf, $cargo, $areaId]);
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
        'area_slug' => $areaSlug,
    ],
    'access' => [
        'is_first_login' => $isFirstLogin,
        'distinct_access_count' => $access['distinct_access_count'],
    ],
]);
