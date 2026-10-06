<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/PortalFichaApi.php';
require_once __DIR__ . '/../../src/PortalSession.php';

/**
 * Diagnóstico de onde vêm nome, cargo, CPF e setor.
 *
 * Mostra só NOMES de campos, nunca valores: quais campos a API de ficha
 * devolve pra quem está logado e quais variáveis a sessão do Portal tem.
 * É o que falta pra saber por que o cargo não chega — se o campo tem outro
 * nome, ou se a API nem responde.
 */

mse_cors();
$admin = mse_require_admin();

$pdo = mse_db();
$stmt = $pdo->prepare('SELECT name, cpf, cargo FROM users WHERE id = ?');
$stmt->execute([(int) $admin['id']]);
$eu = $stmt->fetch() ?: ['name' => '', 'cpf' => null, 'cargo' => null];

$busca = !empty($eu['cpf']) ? (string) $eu['cpf'] : (string) $eu['name'];
$consulta = mse_portal_ficha_consultar($busca);

$sessao = mse_tentar_sessao_portal();

mse_json([
    'ficha_api' => [
        'buscou_por' => !empty($eu['cpf']) ? 'cpf' : 'nome',
        'erro' => $consulta['erro'],
        'encontrou' => $consulta['ficha'] !== null,
        'campos_recebidos' => $consulta['campos'] ?? [],
        'cargo_lido' => $consulta['ficha'] !== null && !empty($consulta['ficha']['funcao']),
    ],
    'sessao_portal' => [
        'variaveis' => mse_sessao_chaves(),
        'achou_login' => $sessao !== null,
        'achou_cargo' => $sessao !== null && !empty($sessao['cargo']),
    ],
    'seu_cadastro' => [
        'tem_cargo' => !empty($eu['cargo']),
        'tem_cpf' => !empty($eu['cpf']),
    ],
]);
