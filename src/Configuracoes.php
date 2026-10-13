<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php'; // mse_tem_tabela()

/**
 * Ajustes gerais da Academy que o admin liga e desliga na tela
 * (tabela configuracoes: chave -> valor). Hoje:
 *
 *   envio_video_colaboradores   '1' (padrão) ou '0': pessoas que não são admin
 *                               podem ou não usar "Adicionar vídeo".
 *
 * Migração 043. Se não rodou, a tabela é criada aqui sozinha. Sem a tabela
 * (ou sem permissão para criar), vale o padrão: liberado.
 */

/** Garante a tabela. false = não existe e não deu pra criar. */
function mse_garantir_configuracoes(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (mse_tem_tabela($pdo, 'configuracoes')) {
        return $ok = true;
    }
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS configuracoes (
               chave VARCHAR(60) NOT NULL, valor VARCHAR(255) NOT NULL,
               atualizado_por VARCHAR(200) NULL, atualizado_em DATETIME NOT NULL,
               PRIMARY KEY (chave)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[configuracoes] Não consegui criar a tabela (rode a migração 043): ' . $e->getMessage());
        return $ok = false;
    }
}

function mse_config_ler(PDO $pdo, string $chave, string $padrao): string
{
    try {
        if (!mse_tem_tabela($pdo, 'configuracoes')) {
            return $padrao;
        }
        $st = $pdo->prepare('SELECT valor FROM configuracoes WHERE chave = ?');
        $st->execute([$chave]);
        $v = $st->fetchColumn();
        return $v === false ? $padrao : (string) $v;
    } catch (Throwable $e) {
        return $padrao;
    }
}

function mse_config_gravar(PDO $pdo, string $chave, string $valor, string $quem): bool
{
    if (!mse_garantir_configuracoes($pdo)) {
        return false;
    }
    $pdo->prepare(
        'INSERT INTO configuracoes (chave, valor, atualizado_por, atualizado_em) VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE valor = VALUES(valor), atualizado_por = VALUES(atualizado_por), atualizado_em = NOW()'
    )->execute([$chave, $valor, mb_substr($quem, 0, 200)]);
    return true;
}

/** Quem não é admin pode enviar vídeo? (padrão: sim) */
function mse_envio_video_liberado(PDO $pdo): bool
{
    return mse_config_ler($pdo, 'envio_video_colaboradores', '1') !== '0';
}

/** Para a trava nos endpoints de envio: recusa quem não é admin se o envio estiver desabilitado. */
function mse_exigir_envio_video_liberado(PDO $pdo, array $usuario): void
{
    if (($usuario['role'] ?? '') !== 'admin' && !mse_envio_video_liberado($pdo)) {
        mse_error('O envio de vídeos está desabilitado pelos administradores no momento.', 403);
    }
}
