<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Departamentos.php';

/**
 * Gerenciar a lista de departamentos (só admin).
 *
 * GET  → todos os nomes que o Portal manda, com "oculto", e os adicionados à mão
 * POST {acao, nome}
 *      ocultar    tira o nome da lista
 *      mostrar    traz de volta um nome oculto
 *      adicionar  cria um departamento à mão
 *      excluir    apaga um departamento adicionado à mão (um do Portal só é ocultado)
 */

mse_cors();
$admin = mse_require_admin();
$pdo = mse_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    mse_json(mse_departamentos_gerenciar($pdo));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$in = mse_input();
$acao = (string) ($in['acao'] ?? '');
$nome = mb_substr(trim((string) preg_replace('/\s+/', ' ', (string) ($in['nome'] ?? ''))), 0, 150);
$chave = $nome === '' ? '' : mb_substr(mse_departamento_chave($nome), 0, 150);
if (!in_array($acao, ['ocultar', 'mostrar', 'adicionar', 'excluir'], true) || $chave === '') {
    mse_error('Informe o nome e a acao (ocultar, mostrar, adicionar ou excluir).', 422);
}
if (!mse_garantir_tabela_departamentos_config($pdo)) {
    mse_error('Não consegui criar a tabela de ajustes. Rode a migração 029 no banco (migrations/029_departamentos_config.sql).', 409);
}

$agora = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
$grava = static function (string $tipo) use ($pdo, $chave, $nome, $admin, $agora): void {
    $pdo->prepare(
        'INSERT INTO departamentos_config (chave, nome, tipo, criado_por, criado_em) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), nome = VALUES(nome), criado_por = VALUES(criado_por), criado_em = VALUES(criado_em)'
    )->execute([$chave, $nome, $tipo, (string) $admin['name'], $agora]);
};

if ($acao === 'adicionar') {
    $grava('extra');
} elseif ($acao === 'excluir') {
    $st = $pdo->prepare('SELECT tipo FROM departamentos_config WHERE chave = ?');
    $st->execute([$chave]);
    if ($st->fetchColumn() === 'extra') {
        $pdo->prepare('DELETE FROM departamentos_config WHERE chave = ?')->execute([$chave]);
    } else {
        $grava('ocultar'); // nome do Portal: não dá para apagar, só esconder
    }
} else {
    $grava($acao === 'ocultar' ? 'ocultar' : 'mostrar');
}

mse_json(['ok' => true] + mse_departamentos_gerenciar($pdo));
