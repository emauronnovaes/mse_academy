<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';
require_once __DIR__ . '/../../../src/PortalFichaApi.php';

/**
 * Atualiza cargo, CPF e departamento dos colaboradores pela ficha do Portal
 * (API ff_infos, a mesma que o login usa).
 *
 * O login só grava esses dados no momento em que a pessoa entra — quem
 * entrou quando a API estava fora, ou antes de ela existir, ficou sem
 * cargo no relatório. Aqui o admin atualiza todo mundo de uma vez.
 *
 * POST {apos_id?: int} — processa um lote de pessoas com id maior que
 * apos_id e devolve proximo_apos_id (null = acabou). A tela chama em
 * sequência: a API leva até 5s por pessoa, e um lote só evita que o
 * servidor corte a requisição no meio.
 *
 * Busca pelo CPF quando a pessoa já tem; sem CPF, pelo nome — e aí só
 * aceita a ficha se o nome bater, senão um homônimo gravaria o cargo de
 * outra pessoa.
 */

const ATUALIZAR_LOTE = 5;

mse_cors();
mse_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$aposId = max(0, (int) ($input['apos_id'] ?? 0));
$pdo = mse_db();

$filtroOculto = mse_tem_coluna($pdo, 'users', 'oculto_em_relatorios') ? ' AND oculto_em_relatorios = 0' : '';
// so_faltando: a tela de relatórios chama sozinha, só pra quem ainda está
// sem cargo ou sem o departamento do Portal — não precisa esperar a
// pessoa entrar de novo.
if (!empty($input['so_faltando'])) {
    $filtroOculto .= mse_garantir_coluna_setor_portal($pdo)
        ? " AND (cargo IS NULL OR cargo = '' OR setor_portal IS NULL OR setor_portal = '')"
        : " AND (cargo IS NULL OR cargo = '')";
}
$total = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE active = 1' . $filtroOculto)->fetchColumn();
$feitos = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE active = 1' . $filtroOculto . ' AND id <= ' . $aposId)->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT id, name, cpf, cargo, area_id FROM users
     WHERE active = 1' . $filtroOculto . ' AND id > ? ORDER BY id ASC LIMIT ' . ATUALIZAR_LOTE
);
$stmt->execute([$aposId]);
$lote = $stmt->fetchAll();

$resultado = ['atualizados' => [], 'sem_ficha' => [], 'nome_diferente' => []];
$ultimoId = $aposId;

foreach ($lote as $u) {
    $ultimoId = (int) $u['id'];
    $porCpf = !empty($u['cpf']);
    $consulta = mse_portal_ficha_buscar_pessoa($u['cpf'] ?? null, (string) $u['name']);

    // Erro da API (token, rede, curl): o mesmo vale pra todo mundo, então
    // para tudo e mostra o motivo em vez de marcar 37 pessoas "sem ficha".
    if ($consulta['erro'] !== null) {
        mse_error($consulta['erro'], 502);
    }

    $ficha = $consulta['ficha'];
    if ($ficha === null) {
        $resultado['sem_ficha'][] = $u['name'];
        continue;
    }

    $campos = [];
    $valores = [];
    if (!empty($ficha['funcao']) && $ficha['funcao'] !== $u['cargo']) {
        $campos[] = 'cargo = ?';
        $valores[] = mb_substr($ficha['funcao'], 0, 150);
    }
    if (!$porCpf && !empty($ficha['cpf']) && strlen($ficha['cpf']) === 11) {
        // CPF é único: se já está em outro cadastro, não mexe.
        $dono = $pdo->prepare('SELECT id FROM users WHERE cpf = ? AND id <> ?');
        $dono->execute([$ficha['cpf'], $u['id']]);
        if ($dono->fetchColumn() === false) {
            $campos[] = 'cpf = ?';
            $valores[] = $ficha['cpf'];
        }
    }
    if (!empty($ficha['obras_departamento'])) {
        $areaId = mse_area_id_por_setor($pdo, $ficha['obras_departamento']);
        if ($areaId !== null && $areaId !== ($u['area_id'] !== null ? (int) $u['area_id'] : null)) {
            $campos[] = 'area_id = ?';
            $valores[] = $areaId;
        }
    }

    mse_gravar_setor_portal($pdo, (int) $u['id'], $ficha['obras_departamento'] ?? null);

    if ($campos) {
        $valores[] = $u['id'];
        $pdo->prepare('UPDATE users SET ' . implode(', ', $campos) . ' WHERE id = ?')->execute($valores);
        $resultado['atualizados'][] = $u['name'];
    }
}

mse_json($resultado + [
    'processados' => $feitos + count($lote),
    'total' => $total,
    'proximo_apos_id' => count($lote) === ATUALIZAR_LOTE ? $ultimoId : null,
]);
