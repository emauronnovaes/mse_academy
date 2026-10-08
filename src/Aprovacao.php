<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';

/**
 * Vídeos enviados por quem não é admin ficam esperando aprovação.
 *
 * O vídeo é gravado em courses como qualquer outro, só que com
 * is_published = 0 e aprovacao_status = 'pendente' — por isso não aparece
 * pra ninguém (todas as telas de aluno já filtram is_published = 1) até um
 * admin aprovar. Recusado continua guardado, com o motivo.
 *
 * Colunas da migração 024. Se ela não rodou, são criadas aqui na primeira
 * vez que alguém envia um vídeo: são colunas vazias que não mexem em dado
 * nenhum (mesmo esquema do setor_portal).
 */

const MSE_COLUNAS_APROVACAO = [
    'aprovacao_status' => 'VARCHAR(10) NULL',
    'enviado_por' => 'INT UNSIGNED NULL',
    'enviado_em' => 'DATETIME NULL',
    'notificado_em' => 'DATETIME NULL',
    'decidido_por' => 'INT UNSIGNED NULL',
    'decidido_em' => 'DATETIME NULL',
    'motivo_recusa' => 'VARCHAR(500) NULL',
];

/** As colunas existem (ou acabaram de ser criadas)? */
function mse_garantir_colunas_aprovacao(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $stmt = $pdo->prepare(
        'SELECT COLUMN_NAME FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute(['courses']);
    $existentes = array_flip(array_column($stmt->fetchAll(), 'COLUMN_NAME'));
    $faltando = array_diff_key(MSE_COLUNAS_APROVACAO, $existentes);
    if (!$faltando) {
        return $ok = true;
    }
    try {
        $partes = [];
        foreach ($faltando as $nome => $tipo) {
            $partes[] = "ADD COLUMN {$nome} {$tipo}";
        }
        $pdo->exec('ALTER TABLE courses ' . implode(', ', $partes));
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[aprovacao] Não consegui criar as colunas (rode a migração 024): ' . $e->getMessage());
        return $ok = false;
    }
}

/**
 * Condição SQL que tira das listas de admin os vídeos ainda em aprovação
 * ou recusados (eles têm tela própria). Vazia se a coluna não existe.
 */
function mse_sql_so_aprovados(PDO $pdo, string $alias = 'c'): string
{
    return mse_tem_coluna($pdo, 'courses', 'aprovacao_status')
        ? "({$alias}.aprovacao_status IS NULL OR {$alias}.aprovacao_status = 'aprovado')"
        : '';
}

/**
 * Admin pode tudo. Quem não é admin só mexe (perguntas, departamentos) no
 * vídeo que ele mesmo enviou e que ainda está esperando aprovação.
 */
function mse_exigir_admin_ou_autor(PDO $pdo, array $user, int $courseId): void
{
    if (($user['role'] ?? '') === 'admin') {
        return;
    }
    if ($courseId > 0 && mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM courses WHERE id = ? AND enviado_por = ? AND aprovacao_status = 'pendente'"
        );
        $stmt->execute([$courseId, (int) $user['id']]);
        if ($stmt->fetchColumn() !== false) {
            return;
        }
    }
    mse_error('Acesso restrito a administradores.', 403);
}

/**
 * Pasta do S3 dos vídeos enviados por quem não é admin. Separada pra
 * ninguém conseguir sobrescrever, de propósito ou sem querer, o arquivo
 * de um treinamento que já está no ar.
 */
const MSE_PASTA_SUGESTOES = 'sugestoes/';

/** Endereço da Academy pros links dos e-mails. */
function mse_url_academy(): string
{
    $fixa = rtrim(trim(mse_env('ACADEMY_URL')), '/');
    if ($fixa !== '') {
        return $fixa;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    // Os endpoints ficam em /api/.../arquivo.php: sobe até a raiz da Academy.
    $caminho = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
    $pos = strpos($caminho, '/api/');
    $raiz = $pos === false ? '' : substr($caminho, 0, $pos);
    return ($https ? 'https' : 'http') . '://' . $host . $raiz;
}
