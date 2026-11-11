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

/**
 * Primeiro nome para a saudação: sai do nome da pessoa. O e-mail só quando
 * não há nome — um e-mail herdado de outra pessoa (vaga, caixa de obra)
 * mostrava o nome errado ("Viviane" para a Helena).
 */
function mse_primeiro_nome(string $nome, string $email): string
{
    $nome = trim($nome);
    if ($nome !== '' && !mse_str_contains($nome, '@') && preg_match('/\p{L}/u', $nome)) {
        $primeiro = preg_split('/\s+/u', $nome)[0];
        return mb_convert_case(mb_strtolower($primeiro, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    return mse_first_name_from_email($email);
}

/** Primeiro nome sem acento e em minúsculas, para comparar. Vazio se não for um nome (e-mail, CPF). */
function mse_primeiro_nome_comparavel(string $nome): string
{
    $nome = trim($nome);
    if ($nome === '' || mse_str_contains($nome, '@')) {
        return '';
    }
    $t = mb_strtolower($nome, 'UTF-8');
    $semAcento = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
    if ($semAcento !== false) {
        $t = $semAcento;
    }
    $t = trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z\s]/', '', $t)));
    return $t === '' ? '' : explode(' ', $t)[0];
}

/**
 * A conta (o e-mail, que é a chave da tabela users) em que esta pessoa
 * entra. Normalmente é o próprio e-mail. Mas se a conta com esse e-mail é
 * de OUTRA pessoa (CPF diferente; sem CPF dos dois lados, primeiro nome
 * diferente) — e-mail herdado de quem saiu, caixa compartilhada da obra,
 * erro na ficha do RH —, entrar nela misturava as duas: uma via o nome e
 * os vídeos da outra. Aí a pessoa vai para a conta do CPF dela (ou uma
 * nova), e a outra fica intacta.
 *
 * Exceção: se o e-mail tem o nome de quem está entrando e não o do dono
 * atual (helena.goes@ entrando, conta com nome Viviane), o e-mail é de quem
 * está entrando. A conta atual sai dele (vai para a chave do CPF dela, com
 * o que tem dentro) e quem entra fica com o próprio e-mail.
 */
function mse_chave_login(PDO $pdo, string $email, ?string $cpf, string $nome): string
{
    $cpf = (string) preg_replace('/\D+/', '', (string) $cpf);
    $cpf = strlen($cpf) === 11 ? $cpf : '';

    $st = $pdo->prepare('SELECT id, name, cpf FROM users WHERE email = ?');
    $st->execute([$email]);
    $dono = $st->fetch();
    if (!$dono) {
        return $email;
    }
    $cpfDono = (string) preg_replace('/\D+/', '', (string) $dono['cpf']);
    $a = mse_primeiro_nome_comparavel((string) $dono['name']);
    $b = mse_primeiro_nome_comparavel($nome);
    if ($cpf !== '' && strlen($cpfDono) === 11) {
        $outraPessoa = $cpfDono !== $cpf;
    } else {
        $outraPessoa = $a !== '' && $b !== '' && $a !== $b;
    }
    if (!$outraPessoa) {
        return $email;
    }

    // O e-mail tem o nome de quem está entrando, e não o do dono atual? Então é dela.
    $nomeDoEmail = mse_primeiro_nome_comparavel(str_replace(['.', '_', '-'], ' ', explode('@', $email)[0]));
    if ($nomeDoEmail !== '' && $nomeDoEmail === $b && $nomeDoEmail !== $a) {
        $chaveDono = strlen($cpfDono) === 11
            ? $cpfDono . '@sem-email.mse.local'
            : 'sem-cpf-' . substr(hash('sha256', $a . '|' . mb_strtolower(trim((string) $dono['name']), 'UTF-8') . '|' . $email), 0, 16) . '@sem-email.mse.local';
        $st = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $st->execute([$chaveDono]);
        if ((int) $st->fetchColumn() === 0) {
            $pdo->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$chaveDono, (int) $dono['id']]);
            error_log("[login] O e-mail {$email} é de {$nome}: a conta #{$dono['id']} ({$dono['name']}) passou para {$chaveDono}.");
            // Quem entra já tinha conta separada pelo CPF? Ela fica com o e-mail.
            if ($cpf !== '') {
                $pdo->prepare('UPDATE users SET email = ? WHERE cpf = ?')->execute([$email, $cpf]);
            }
            return $email;
        }
    }

    error_log("[login] O e-mail {$email} é da conta #{$dono['id']} ({$dono['name']}), mas quem entrou foi {$nome}: entra numa conta separada.");
    if ($cpf !== '') {
        $st = $pdo->prepare('SELECT email FROM users WHERE cpf = ?');
        $st->execute([$cpf]);
        $doCpf = $st->fetchColumn();
        return $doCpf !== false ? (string) $doCpf : $cpf . '@sem-email.mse.local';
    }
    return 'sem-cpf-' . substr(hash('sha256', mse_primeiro_nome_comparavel($nome) . '|' . mb_strtolower(trim($nome), 'UTF-8') . '|' . $email), 0, 16)
        . '@sem-email.mse.local';
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

    // Log de consulta (consultasmia_2): quando a pessoa entrou. Nunca
    // atrapalha o login (a função engole qualquer erro).
    require_once __DIR__ . '/Viniconsultas.php';
    mse_vini_registrar_evento($userId, 'login', ['origem' => 'academy']);

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
