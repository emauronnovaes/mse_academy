<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Response.php';

const MSE_TOKEN_TTL_DAYS = 30;

/** Gera um token aleatório e criptograficamente seguro (64 caracteres hex). */
function mse_generate_token(): string
{
    return bin2hex(random_bytes(32));
}

/** Nunca guardamos o token puro no banco — só o hash dele. */
function mse_hash_token(string $token): string
{
    return hash('sha256', $token);
}

/** Cria uma sessão (linha em auth_tokens) e devolve o token pro cliente. */
function mse_create_session(int $userId): string
{
    $pdo = mse_db();
    $token = mse_generate_token();
    $hash = mse_hash_token($token);
    $expiresAt = (new DateTime())->modify('+' . MSE_TOKEN_TTL_DAYS . ' days')->format('Y-m-d H:i:s');
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $stmt = $pdo->prepare(
        'INSERT INTO auth_tokens (user_id, token_hash, expires_at, user_agent) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $hash, $expiresAt, $userAgent]);

    return $token;
}

// ------------------------------------------------------------
// Nome derivado do e-mail + contagem de acessos distintos
// ------------------------------------------------------------

/**
 * Extrai um primeiro nome pra saudação a partir do e-mail.
 * "matheus.batista@mse.com.br" -> "Matheus"
 * "joao@mse.com.br"            -> "Joao"
 * E-mail malformado (sem @, ou só números)  -> "Colaborador" (nunca quebra a tela)
 */
function mse_first_name_from_email(string $email): string
{
    $email = trim($email);
    if (!mse_str_contains($email, '@')) {
        return 'Colaborador'; // não é um e-mail válido, nem tenta adivinhar
    }
    $local = explode('@', $email)[0];
    $firstPart = explode('.', $local)[0];
    // remove números e símbolos residuais (ex: "matheus123" -> "matheus")
    $firstPart = preg_replace('/[^a-zA-ZÀ-ÖØ-öø-ÿ]/u', '', $firstPart);
    if ($firstPart === '' || $firstPart === null) {
        return 'Colaborador';
    }
    return mb_convert_case(mb_strtolower($firstPart, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Registra o acesso de hoje e devolve quantos DIAS DIFERENTES a pessoa já
 * acessou no total. Entrar 20 vezes no mesmo dia conta como 1 só — só
 * incrementa quando a data de hoje é diferente da última salva.
 *
 * O UPDATE já leva a condição "só se a data for diferente" dentro do
 * próprio WHERE — isso é atômico de verdade porque o MySQL trava a linha
 * durante o UPDATE. Se vários acessos da mesma pessoa chegarem juntos, só
 * o primeiro consegue mudar a linha; os outros caem no WHERE e não batem
 * mais (a data já foi trocada pra hoje pelo primeiro), então não somam de
 * novo. Testado sob 10 chamadas simultâneas.
 *
 * @return array{distinct_access_count:int, accessed_today_already:bool}
 */
function mse_track_access(int $userId): array
{
    $pdo = mse_db();
    $today = (new DateTime())->format('Y-m-d');

    $stmt = $pdo->prepare(
        'UPDATE users
         SET distinct_access_count = distinct_access_count + 1, last_access_date = ?
         WHERE id = ? AND (last_access_date IS NULL OR last_access_date <> ?)'
    );
    $stmt->execute([$today, $userId, $today]);
    $incrementedAgora = $stmt->rowCount() === 1;

    $stmt = $pdo->prepare('SELECT distinct_access_count FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $count = (int) $stmt->fetchColumn();

    return ['distinct_access_count' => $count, 'accessed_today_already' => !$incrementedAgora];
}

/** Lê o header Authorization e devolve o usuário logado (ou null). */
function mse_current_user(): ?array
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (!preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
        return null;
    }

    $hash = mse_hash_token($matches[1]);
    $pdo = mse_db();

    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.email, u.role, u.area_id, u.cargo, a.slug AS area_slug
         FROM auth_tokens t
         JOIN users u ON u.id = t.user_id
         LEFT JOIN areas a ON a.id = u.area_id
         WHERE t.token_hash = ? AND t.expires_at > NOW() AND u.active = 1
         LIMIT 1'
    );
    $stmt->execute([$hash]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/** Exige login. Encerra a requisição com 401 se não houver usuário autenticado. */
function mse_require_auth(): array
{
    $user = mse_current_user();
    if ($user === null) {
        mse_error('Não autenticado.', 401);
    }
    return $user;
}

/** Exige que o usuário logado seja admin. */
function mse_require_admin(): array
{
    $user = mse_require_auth();
    if ($user['role'] !== 'admin') {
        mse_error('Acesso restrito a administradores.', 403);
    }
    return $user;
}
