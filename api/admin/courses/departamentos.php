<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';
require_once __DIR__ . '/../../../src/Departamentos.php';

/**
 * Para quais departamentos do Portal um vídeo é obrigatório.
 *
 * Vídeo sem nenhum departamento marcado (aqui e em course_areas) vale para
 * todo mundo. Marcar RESTRINGE a obrigatoriedade, nunca a visibilidade:
 * quem é de outro departamento continua vendo e podendo assistir.
 *
 * GET  ?course_id=N                    → lista os departamentos e os marcados
 * POST {course_id, departamentos:[nomes]} → substitui a seleção
 *
 * Admin, ou quem enviou o vídeo (só o dele).
 */

mse_cors();
$usuario = mse_require_auth();

$pdo = mse_db();
$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? mse_input() : [];
$courseId = (int) ($_SERVER['REQUEST_METHOD'] === 'GET' ? ($_GET['course_id'] ?? 0) : ($input['course_id'] ?? 0));
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}
mse_exigir_admin_ou_dono($pdo, $usuario, $courseId);

$stmt = $pdo->prepare('SELECT id, title FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$curso = $stmt->fetch();
if (!$curso) {
    mse_error('Aula não encontrada.', 404);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $marcados = mse_departamentos_do_curso($pdo, $courseId);
    // O que já estava marcado pelas áreas da Academy (course_areas) aparece
    // marcado também, pelo nome da área: ao salvar, vira departamento.
    if (mse_tem_tabela($pdo, 'course_areas')) {
        $st = $pdo->prepare('SELECT a.name FROM course_areas ca JOIN areas a ON a.id = ca.area_id WHERE ca.course_id = ?');
        $st->execute([$courseId]);
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $nomeArea) {
            $marcados[] = $nomeArea;
        }
    }
    $chaves = array_flip(array_map('mse_departamento_chave', $marcados));
    $lista = mse_departamentos_portal($pdo)['departamentos'];
    // Marcado que não está mais na lista do Portal continua aparecendo.
    foreach ($marcados as $m) {
        if (!in_array(mse_departamento_chave($m), array_map('mse_departamento_chave', $lista), true)) {
            $lista[] = $m;
        }
    }
    mse_json([
        'course' => ['id' => (int) $curso['id'], 'title' => $curso['title']],
        'departamentos' => array_map(static fn($d) => ['nome' => $d, 'marcado' => isset($chaves[mse_departamento_chave($d)])], $lista),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$enviados = $input['departamentos'] ?? [];
if (!is_array($enviados) || count($enviados) > 100) {
    mse_error('departamentos precisa ser uma lista de nomes (até 100).', 422);
}
$escolhidos = [];
foreach ($enviados as $nome) {
    $nome = mb_substr(trim((string) preg_replace('/\s+/', ' ', (string) $nome)), 0, 150);
    $chave = $nome === '' ? '' : mse_departamento_chave($nome);
    if ($chave !== '') {
        $escolhidos[$chave] = $nome;
    }
}

// Sem departamento escolhido, só precisa limpar se a tabela já existir; com
// escolha, cria a tabela se faltar. (Usa o retorno de mse_garantir..., e não
// mse_tem_tabela: esta guarda a resposta e não veria a tabela recém-criada.)
$temTabela = $escolhidos ? mse_garantir_tabela_departamentos($pdo) : mse_tem_tabela($pdo, 'course_departamentos');
if ($escolhidos && !$temTabela) {
    mse_error('Este servidor ainda não guarda departamentos por vídeo. Falta rodar a migração 025 no banco (migrations/025_departamentos_obrigatorios.sql).', 409);
}
if ($temTabela) {
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM course_departamentos WHERE course_id = ?')->execute([$courseId]);
        $ins = $pdo->prepare('INSERT INTO course_departamentos (course_id, departamento, departamento_norm) VALUES (?, ?, ?)');
        foreach ($escolhidos as $chave => $nome) {
            $ins->execute([$courseId, $nome, mb_substr($chave, 0, 150)]);
        }
        // A seleção desta tela é a completa: as áreas antigas (course_areas)
        // já apareceram marcadas e, se continuam marcadas, foram gravadas
        // acima como departamento — então saem de lá pra não ficarem
        // valendo escondidas.
        if (mse_tem_tabela($pdo, 'course_areas')) {
            $pdo->prepare('DELETE FROM course_areas WHERE course_id = ?')->execute([$courseId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        mse_error('Não consegui salvar os departamentos. Nada foi alterado.', 500);
    }
}

mse_json([
    'course_id' => $courseId,
    'departamentos' => array_values($escolhidos),
    'vale_para_todos' => !$escolhidos,
    'message' => $escolhidos
        ? 'Agora é obrigatório para ' . count($escolhidos) . ' departamento(s): ' . implode(', ', $escolhidos) . '.'
        : 'Nenhum departamento marcado: obrigatório para todos.',
]);
