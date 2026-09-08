<?php
declare(strict_types=1);

/**
 * Carrega variáveis de ambiente de um arquivo .env simples.
 * Sem dependências externas (nada de Composer) — só PHP puro,
 * pra rodar em qualquer hospedagem que já tenha PHP + MySQL.
 */
function mse_load_env(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        $key = trim($parts[0] ?? '');
        $value = trim($parts[1] ?? '', " \t\n\r\0\x0B\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
        }
    }
}

/** Lê uma variável de ambiente já carregada, com valor padrão. Função
 *  única e compartilhada — usada por src/AwsS3.php e src/AwsClient.php,
 *  entre outros — pra nunca ter duas declarações da mesma função em
 *  arquivos diferentes (isso quebraria com "Cannot redeclare"). */
function mse_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

mse_load_env(__DIR__ . '/../.env');

/**
 * Retorna uma conexão PDO única (reaproveitada durante a requisição).
 * ATTR_PERSISTENT ajuda quando há muitos usuários simultâneos, evitando
 * abrir/fechar conexão TCP com o MySQL a cada requisição.
 */
function mse_db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'mse_academy';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // usa prepared statements de verdade do MySQL
            PDO::ATTR_PERSISTENT => true,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Falha ao conectar ao banco de dados.']);
        exit;
    }

    return $pdo;
}
