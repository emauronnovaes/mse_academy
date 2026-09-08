<?php
declare(strict_types=1);

/**
 * Limpeza de manutenção — roda via cron, NÃO é chamado pelo navegador.
 *
 * Sem isso, "auth_tokens" cresce pra sempre (cada login SSO cria uma
 * linha nova, nada nunca é apagado). Com poucas pessoas usando não dá
 * pra notar, mas com a empresa inteira entrando todo dia essa tabela
 * fica grande rápido, e toda consulta de sessão passa a ficar mais
 * lenta (mesmo com índice, quanto mais linha, mais trabalho).
 *
 * Como agendar (roda 1x por dia, de madrugada):
 *   crontab -e
 *   0 3 * * * php /caminho/completo/scripts/cleanup_maintenance.php >> /var/log/mse-academy-cleanup.log 2>&1
 *
 * Também dá pra rodar manualmente quando quiser:
 *   php scripts/cleanup_maintenance.php
 */

require_once __DIR__ . '/../config/database.php';

$pdo = mse_db();
$startedAt = microtime(true);

// ------------------------------------------------------------
// 1) Sessões (auth_tokens) já expiradas — não servem pra mais nada,
//    ficar com elas só deixa a consulta de "quem está logado" mais lenta.
// ------------------------------------------------------------
$stmt = $pdo->prepare('DELETE FROM auth_tokens WHERE expires_at < NOW()');
$stmt->execute();
$tokensRemoved = $stmt->rowCount();

// ------------------------------------------------------------
// 2) Respostas de quiz (quiz_attempts) com mais de 180 dias — mantemos
//    um histórico razoável pra auditoria/relatório, mas sem acumular
//    pra sempre. Ajuste o intervalo se sua empresa precisar guardar mais.
// ------------------------------------------------------------
$stmt = $pdo->prepare('DELETE FROM quiz_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 180 DAY)');
$stmt->execute();
$quizAttemptsRemoved = $stmt->rowCount();

$elapsed = round(microtime(true) - $startedAt, 3);

echo "[" . date('Y-m-d H:i:s') . "] Limpeza concluída em {$elapsed}s — ";
echo "{$tokensRemoved} sessão(ões) expirada(s), ";
echo "{$quizAttemptsRemoved} resposta(s) de quiz antiga(s) removidas.\n";
