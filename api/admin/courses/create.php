<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin(); // só quem tem role='admin' passa daqui

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();

$areaSlug = trim((string) ($input['area_slug'] ?? ''));
$type = (string) ($input['type'] ?? 'curso'); // 'curso' (catálogo) ou 'onboarding' (trilha obrigatória)
$title = trim((string) ($input['title'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$videoSource = (string) ($input['video_source'] ?? 'youtube'); // 'youtube', 's3' ou 'playlist'
// Aulas do mesmo grupo_sorteio são alternativas: cada pessoa vê só uma.
$grupoSorteio = trim((string) ($input['grupo_sorteio'] ?? ''));
// Aula opcional aparece na trilha mas não trava o avanço nem é exigida
// pra abrir o baú.
$obrigatorio = array_key_exists('obrigatorio', $input) ? (int) (bool) $input['obrigatorio'] : 1;
$youtubeId = trim((string) ($input['youtube_id'] ?? ''));
$videoKey = trim((string) ($input['video_key'] ?? ''));
$durationMinutes = (int) ($input['duration_minutes'] ?? 0);
$orderIndex = $input['order_index'] ?? null; // null = vai pro final da fila automaticamente

// ------------------------------------------------------------
// Validação — os mesmos erros que travariam o front-end depois,
// só que aqui, antes de gravar qualquer coisa errada no banco.
// ------------------------------------------------------------
if (!in_array($type, ['curso', 'onboarding'], true)) {
    mse_error('type precisa ser "curso" ou "onboarding".', 422);
}
// Vídeos de integração (onboarding) aparecem igual pra todo mundo,
// não importa a área da pessoa — por isso não exigimos área pra eles.
// Só o catálogo (type='curso') precisa de uma área pra recomendação
// funcionar.
if ($type === 'curso' && $areaSlug === '') {
    mse_error('Informe area_slug (ex: "financeiro", "ti", "obras").', 422);
}
if ($title === '') {
    mse_error('Informe o título do vídeo.', 422);
}
if (!in_array($videoSource, ['youtube', 's3', 'playlist'], true)) {
    mse_error('video_source precisa ser "youtube", "s3" ou "playlist".', 422);
}
if ($videoSource === 'youtube') {
    if ($youtubeId === '' || !preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId)) {
        mse_error('youtube_id inválido — precisa ter exatamente 11 caracteres (o trecho depois de "v=" na URL do YouTube).', 422);
    }
} elseif ($videoSource === 'playlist') {
    // Id de playlist é mais longo e variável (começa com PL, UU, OL...),
    // por isso não dá pra usar a mesma validação de 11 caracteres.
    if ($youtubeId === '' || !preg_match('/^[A-Za-z0-9_-]{12,64}$/', $youtubeId)) {
        mse_error('Id da playlist inválido — é o trecho depois de "list=" na URL do YouTube.', 422);
    }
    // A Academy não controla o que é assistido dentro de uma playlist,
    // então esse tipo de aula nasce opcional: não faria sentido travar a
    // trilha esperando uma conclusão que nunca vai ser registrada.
    $obrigatorio = 0;
} else {
    // video_source === 's3' — o vídeo já foi enviado antes via
    // /api/admin/media/upload.php, aqui só recebemos o caminho dele.
    if ($videoKey === '') {
        mse_error('Informe video_key (o caminho devolvido pelo upload em /api/admin/media/upload.php).', 422);
    }
    $youtubeId = null; // coluna é NULLABLE desde a migração 008
}
if ($durationMinutes < 0 || $durationMinutes > 600) {
    mse_error('duration_minutes fora do intervalo esperado (0 a 600).', 422);
}

$pdo = mse_db();

$areaId = null;
if ($areaSlug !== '') {
    $stmt = $pdo->prepare('SELECT id FROM areas WHERE slug = ?');
    $stmt->execute([$areaSlug]);
    $area = $stmt->fetch();
    if (!$area) {
        mse_error("Área \"{$areaSlug}\" não existe. Veja os slugs válidos em GET /api/courses/list.php ou na tabela areas.", 422);
    }
    $areaId = (int) $area['id'];
}

// Pergunta do quiz é opcional na criação — dá pra criar o vídeo primeiro
// e adicionar a pergunta depois, mas se vier, valida ela inteira também
// (nunca grava pergunta sem resposta certa, ou com 0/1 opção só).
$quizQuestion = isset($input['quiz_question']) ? trim((string) $input['quiz_question']) : null;
$quizOptions = $input['quiz_options'] ?? null;

if ($quizQuestion !== null && $quizQuestion !== '') {
    if (!is_array($quizOptions) || count($quizOptions) < 2) {
        mse_error('Se enviar quiz_question, quiz_options precisa ter pelo menos 2 opções.', 422);
    }
    $correctCount = 0;
    foreach ($quizOptions as $opt) {
        if (empty($opt['text']) || trim((string) $opt['text']) === '') {
            mse_error('Toda opção do quiz precisa ter "text" preenchido.', 422);
        }
        if (!empty($opt['is_correct'])) {
            $correctCount++;
        }
    }
    if ($correctCount !== 1) {
        mse_error('Exatamente 1 opção do quiz precisa estar marcada como is_correct=true (encontrei ' . $correctCount . ').', 422);
    }
}

// ------------------------------------------------------------
// order_index automático: se não informado, vai pro fim da fila
// dentro do mesmo type (não mistura ordem de onboarding com catálogo).
// ------------------------------------------------------------
if ($orderIndex === null) {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(order_index), 0) + 1 AS next_order FROM courses WHERE type = ?');
    $stmt->execute([$type]);
    $orderIndex = (int) $stmt->fetch()['next_order'];
} else {
    $orderIndex = (int) $orderIndex;
}

// Curso + pergunta + opções tudo junto numa transação — se qualquer
// parte falhar, desfaz tudo (não deixa vídeo "órfão" sem pergunta
// pela metade, nem pergunta sem curso).
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
    // Enquanto a migração não roda no servidor, grava sem as colunas
    // novas em vez de recusar o cadastro inteiro.
    $colunas = [];
    foreach ($pdo->query('SHOW COLUMNS FROM courses') as $col) {
        $colunas[$col['Field']] = true;
    }
    $temNovas = isset($colunas['grupo_sorteio']) && isset($colunas['obrigatorio']);

    if ($temNovas) {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (area_id, type, grupo_sorteio, obrigatorio, title, description, video_source, youtube_id, video_key, duration_minutes, order_index, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([$areaId, $type, $grupoSorteio ?: null, $obrigatorio, $title, $description, $videoSource, $youtubeId, $videoKey ?: null, $durationMinutes, $orderIndex]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (area_id, type, title, description, video_source, youtube_id, video_key, duration_minutes, order_index, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([$areaId, $type, $title, $description, $videoSource, $youtubeId, $videoKey ?: null, $durationMinutes, $orderIndex]);
    }
    $courseId = (int) $pdo->lastInsertId();

    $questionId = null;
    if ($quizQuestion !== null && $quizQuestion !== '') {
        $stmt = $pdo->prepare('INSERT INTO quiz_questions (course_id, question_text, order_index) VALUES (?, ?, 1)');
        $stmt->execute([$courseId, $quizQuestion]);
        $questionId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES (?, ?, ?, ?)');
        foreach ($quizOptions as $i => $opt) {
            $stmt->execute([$questionId, trim((string) $opt['text']), !empty($opt['is_correct']) ? 1 : 0, $i + 1]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Falha ao salvar. Nada foi gravado (a transação desfez tudo). Detalhe: ' . $e->getMessage(), 500);
}

mse_json([
    'course' => [
        'id' => $courseId,
        'area_slug' => $areaSlug,
        'type' => $type,
        'title' => $title,
        'video_source' => $videoSource,
        'youtube_id' => $youtubeId,
        'video_key' => $videoKey ?: null,
        'order_index' => $orderIndex,
    ],
    'quiz_question_id' => $questionId,
], 201);
