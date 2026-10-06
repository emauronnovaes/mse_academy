<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';

/**
 * Relatório por pessoa (aba "Por pessoa" da Busca de treinamentos).
 *
 * GET, todos opcionais:
 *   q                  nome, e-mail ou CPF
 *   tipo, norma        contam só treinamentos desse tipo/norma
 *   data_de, data_ate  contam só participação no período
 *   modalidade         presencial = lista vazia (não existe na Academy)
 *   user_id            ficha de uma pessoa: os treinamentos dela
 *   com_treinamentos   1 = inclui a ficha de cada pessoa (exportar)
 */

mse_cors();
mse_require_admin();

$pdo = mse_db();
$filtros = [
    'q' => (string) ($_GET['q'] ?? ''),
    'tipo' => (string) ($_GET['tipo'] ?? ''),
    'norma' => (string) ($_GET['norma'] ?? ''),
    'data_de' => (string) ($_GET['data_de'] ?? ''),
    'data_ate' => (string) ($_GET['data_ate'] ?? ''),
];
$presencial = ($_GET['modalidade'] ?? '') === 'presencial';

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId > 0) {
    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.email, u.cpf, u.cargo, a.name AS area_name, u.last_access_date
         FROM users u LEFT JOIN areas a ON a.id = u.area_id WHERE u.id = ?'
    );
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) {
        mse_error('Pessoa não encontrada.', 404);
    }
    mse_json([
        'pessoa' => [
            'user_id' => (int) $u['id'],
            'nome' => $u['name'],
            'email' => $u['email'],
            'cpf' => $u['cpf'],
            'cargo' => $u['cargo'],
            'departamento' => $u['area_name'],
            'ultimo_acesso' => $u['last_access_date'],
        ],
        // A busca por nome/CPF serve pra achar a pessoa; dentro da ficha
        // dela não filtra nada.
        'treinamentos' => $presencial ? [] : mse_treinamentos_da_pessoa($pdo, $userId, ['q' => ''] + $filtros),
    ]);
}

$pessoas = $presencial ? [] : mse_lista_pessoas($pdo, $filtros);
if (!empty($_GET['com_treinamentos'])) {
    foreach ($pessoas as &$p) {
        $p['lista_treinamentos'] = $p['treinamentos'] > 0 ? mse_treinamentos_da_pessoa($pdo, $p['user_id'], $filtros) : [];
    }
    unset($p);
}
mse_json(['pessoas' => $pessoas]);
