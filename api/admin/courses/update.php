<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';
require_once __DIR__ . '/../../../src/Assuntos.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';
require_once __DIR__ . '/../../../src/Classificacao.php';

mse_cors();
// Admin, ou o dono do vídeo (quem o enviou) — só o dele. Conferido abaixo,
// quando o course_id é conhecido.
$usuario = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

/**
 * Edita os textos de uma aula (título e descrição). Não mexe em vídeo,
 * área nem quiz — trocar o nome de uma aula não deveria exigir recriar
 * nada disso, nem arriscar perder o histórico de quem já assistiu.
 *
 * Só os campos enviados são alterados: mandar apenas "title" deixa a
 * descrição como está.
 */

$input = mse_input();

$courseId = (int) ($input['course_id'] ?? 0);
if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$pdo = mse_db();
mse_exigir_admin_ou_dono($pdo, $usuario, $courseId);

$stmt = $pdo->prepare('SELECT id, title, description FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    mse_error('Aula não encontrada.', 404);
}

$campos = [];
$valores = [];

if (array_key_exists('title', $input)) {
    $title = trim((string) $input['title']);
    if ($title === '') {
        mse_error('O título não pode ficar vazio.', 422);
    }
    if (mb_strlen($title) > 200) {
        mse_error('Título longo demais (máximo 200 caracteres).', 422);
    }
    $campos[] = 'title = ?';
    $valores[] = $title;
}

if (array_key_exists('description', $input)) {
    $campos[] = 'description = ?';
    $valores[] = trim((string) $input['description']);
}

// Onde o vídeo aparece (usado na tela de aprovação, ao editar antes de aprovar).
if (array_key_exists('duration_minutes', $input)) {
    $dur = (int) $input['duration_minutes'];
    if ($dur < 0 || $dur > 600) {
        mse_error('duration_minutes fora do intervalo esperado (0 a 600).', 422);
    }
    $campos[] = 'duration_minutes = ?';
    $valores[] = $dur;
}
if (array_key_exists('type', $input)) {
    $tipo = (string) $input['type'];
    if (!in_array($tipo, ['curso', 'onboarding'], true)) {
        mse_error('type precisa ser "curso" ou "onboarding".', 422);
    }
    $areaId = null;
    $areaIds = [];
    if ($tipo === 'curso') {
        $areaIds = mse_ler_assuntos($pdo, $input);
        // Sem assunto só pode se ainda há um tema sugerido esperando aprovação.
        $temaPendente = false;
        if (!$areaIds && mse_tem_coluna($pdo, 'courses', 'tema_status')) {
            $st = $pdo->prepare('SELECT tema_status FROM courses WHERE id = ?');
            $st->execute([$courseId]);
            $temaPendente = $st->fetchColumn() === 'pendente';
        }
        if (!$areaIds && !$temaPendente) {
            mse_error('Escolha pelo menos um assunto do curso.', 422);
        }
        $areaId = $areaIds[0] ?? null;
    }
    $gravarAssuntos = true;
    $st = $pdo->prepare('SELECT type FROM courses WHERE id = ?');
    $st->execute([$courseId]);
    if ($st->fetchColumn() !== $tipo) {
        // Mudou de tipo: vai pro fim da fila do tipo novo (a ordem é por tipo).
        $st = $pdo->prepare('SELECT COALESCE(MAX(order_index), 0) + 1 FROM courses WHERE type = ?');
        $st->execute([$tipo]);
        $campos[] = 'order_index = ?';
        $valores[] = (int) $st->fetchColumn();
    }
    $campos[] = 'type = ?';
    $valores[] = $tipo;
    $campos[] = 'area_id = ?';
    $valores[] = $areaId === null ? null : (int) $areaId;
}

// Dados de auditoria (tipo, normas, instrutor, conteúdo, assuntos): só
// os que vierem no corpo são alterados.
foreach (mse_ler_campos_auditoria($pdo, $input) + mse_ler_aviso_presenca($pdo, $input) as $coluna => $valor) {
    $campos[] = $coluna . ' = ?';
    $valores[] = $valor;
}

// Palavras-chave e recomendado/obrigatório (só os que vierem no corpo).
$palavras = mse_ler_palavras_chave($input, false);
$obrig = mse_ler_obrigatoriedade($input, false);
if (array_key_exists('somente_obra', $input)) {
    if (!mse_garantir_colunas_classificacao($pdo)) {
        mse_error('Falta rodar a migração 040 no banco (migrations/040_classificacao_de_videos.sql).', 409);
    }
    $campos[] = 'somente_obra = ?';
    $valores[] = !empty($input['somente_obra']) ? 1 : 0;
}
if ($palavras !== null || $obrig !== null) {
    if (!mse_garantir_colunas_classificacao($pdo)) {
        mse_error('Falta rodar a migração 040 no banco (migrations/040_classificacao_de_videos.sql).', 409);
    }
    if (array_key_exists('palavras_chave', $input)) {
        if ($palavras === null || count(explode(', ', $palavras)) < 3) {
            mse_error('Escreva pelo menos 3 palavras-chave.', 422);
        }
        $campos[] = 'palavras_chave = ?';
        $valores[] = $palavras;
    }
    if ($obrig !== null) {
        $campos[] = 'obrigatoriedade = ?';
        $valores[] = $obrig;
    }
}

if (!$campos) {
    mse_error('Nada para alterar — envie title, description, tipo, duração ou os dados de auditoria.', 422);
}

$valores[] = $courseId;
$stmt = $pdo->prepare('UPDATE courses SET ' . implode(', ', $campos) . ' WHERE id = ?');
$stmt->execute($valores);

// Todos os assuntos do vídeo (só quando o tipo veio junto, como na edição da aprovação).
if (!empty($gravarAssuntos)) {
    if (count($areaIds) > 1 || mse_tem_assuntos($pdo)) {
        mse_gravar_assuntos($pdo, $courseId, $areaIds);
    }
}

$stmt = $pdo->prepare('SELECT id, title, description FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$atualizado = $stmt->fetch();

// Aulas mudaram: o banco de consulta se atualiza em até 1 minuto.
require_once __DIR__ . '/../../../src/Viniconsultas.php';
mse_vini_recalcular_agendado(true);

mse_json([
    'course' => [
        'id' => (int) $atualizado['id'],
        'title' => $atualizado['title'],
        'description' => $atualizado['description'],
    ],
    'titulo_anterior' => $course['title'],
    'message' => 'Aula atualizada.',
]);
