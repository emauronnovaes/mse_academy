<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Treinamentos.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';
require_once __DIR__ . '/../../../src/Assuntos.php';

mse_cors();
// Qualquer pessoa logada pode cadastrar. Admin publica na hora; os demais
// mandam para aprovação (o vídeo fica escondido até um admin aprovar).
$usuario = mse_require_auth();
$paraAprovacao = $usuario['role'] !== 'admin';

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
$pediuAssunto = $areaSlug !== '' || (isset($input['area_slugs']) && is_array($input['area_slugs']) && count($input['area_slugs']) > 0);
if ($type === 'curso' && !$pediuAssunto) {
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
    if ($paraAprovacao && !mse_str_starts_with(ltrim($videoKey, '/'), MSE_PASTA_SUGESTOES)) {
        mse_error('Arquivo de vídeo inválido para envio.', 422);
    }
}
if ($durationMinutes < 0 || $durationMinutes > 600) {
    mse_error('duration_minutes fora do intervalo esperado (0 a 600).', 422);
}

$pdo = mse_db();

// Antes da transação: criar coluna (ALTER) encerraria a transação aberta.
if ($paraAprovacao && !mse_garantir_colunas_aprovacao($pdo)) {
    mse_error('Este servidor ainda não aceita envio para aprovação. Falta rodar a migração 024 no banco (migrations/024_aprovacao_de_videos.sql).', 409);
}

// Assuntos do vídeo (os cartões da seção Cursos): vários possíveis; o
// primeiro vira o principal (courses.area_id).
$areaIds = mse_ler_assuntos($pdo, $input);
$areaId = $areaIds[0] ?? null;

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

// Enquanto a migração não roda no servidor, grava sem as colunas novas
// em vez de recusar o cadastro inteiro.
$colunas = [];
$tipoVideoSource = '';
foreach ($pdo->query('SHOW COLUMNS FROM courses') as $col) {
    $colunas[$col['Field']] = true;
    if ($col['Field'] === 'video_source') {
        $tipoVideoSource = (string) $col['Type'];
    }
}
$temNovas = isset($colunas['grupo_sorteio']) && isset($colunas['obrigatorio']);

// Playlist e sorteio, diferente das outras colunas, não dá pra
// "degradar": sem a migração o dado simplesmente não cabe no banco.
// Melhor dizer o que falta do que devolver erro de SQL cru — ou pior,
// gravar errado (fora do modo estrito o MySQL aceita e guarda vazio).
// Conferido ANTES de abrir a transação, pra não ter nada pra desfazer.
if ($videoSource === 'playlist' && !mse_str_contains($tipoVideoSource, "'playlist'")) {
    mse_error('Este servidor ainda não aceita playlist. Falta rodar a migração 014 no banco (migrations/014_sorteio_e_playlist.sql).', 409);
}
if ($grupoSorteio !== '' && !$temNovas) {
    mse_error('Este servidor ainda não aceita grupo de sorteio. Falta rodar a migração 014 no banco (migrations/014_sorteio_e_playlist.sql).', 409);
}

// Dados de auditoria (opcionais): validados antes de abrir a transação,
// pra um tipo ou norma inválida não deixar meio cadastro pra trás.
$camposAuditoria = mse_ler_campos_auditoria($pdo, $input) + mse_ler_aviso_presenca($pdo, $input);

// Dados de auditoria automáticos: o que não veio do formulário é preenchido
// com quem enviou (instrutor), a descrição (conteúdo programático) e o
// título (assuntos). O tipo fica em branco = automático (Integração na
// trilha, Treinamento interno no catálogo). Normas não dá pra deduzir:
// ficam em branco até alguém marcar em Treinamentos > Editar.
if (mse_tem_campos_auditoria($pdo)) {
    $automatico = [
        'instrutor' => mb_substr((string) $usuario['name'], 0, 150),
        'conteudo_programatico' => mb_substr($description, 0, 5000),
        'assuntos' => mb_substr($title, 0, 500),
    ];
    foreach ($automatico as $campo => $valor) {
        if (!isset($camposAuditoria[$campo]) && trim($valor) !== '') {
            $camposAuditoria[$campo] = $valor;
        }
    }
}

// A tabela de assuntos é criada ANTES da transação (criar tabela dentro dela
// encerraria a transação). Com um assunto só, usa a tabela se ela já existe.
$usaTabelaAssuntos = count($areaIds) > 1 ? mse_garantir_tabela_assuntos($pdo) : mse_tem_assuntos($pdo);

// Curso + pergunta + opções tudo junto numa transação — se qualquer
// parte falhar, desfaz tudo.
$pdo->beginTransaction();
try {
    if ($temNovas) {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (area_id, type, grupo_sorteio, obrigatorio, title, description, video_source, youtube_id, video_key, duration_minutes, order_index, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$areaId, $type, $grupoSorteio ?: null, $obrigatorio, $title, $description, $videoSource, $youtubeId, $videoKey ?: null, $durationMinutes, $orderIndex, $paraAprovacao ? 0 : 1]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (area_id, type, title, description, video_source, youtube_id, video_key, duration_minutes, order_index, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$areaId, $type, $title, $description, $videoSource, $youtubeId, $videoKey ?: null, $durationMinutes, $orderIndex, $paraAprovacao ? 0 : 1]);
    }
    $courseId = (int) $pdo->lastInsertId();
    if ($usaTabelaAssuntos) {
        mse_gravar_assuntos($pdo, $courseId, $areaIds);
    }

    if ($paraAprovacao) {
        $pdo->prepare(
            "UPDATE courses SET aprovacao_status = 'pendente', enviado_por = ?, enviado_em = NOW() WHERE id = ?"
        )->execute([(int) $usuario['id'], $courseId]);
    }

    if ($camposAuditoria) {
        $stmt = $pdo->prepare(
            'UPDATE courses SET ' . implode(' = ?, ', array_keys($camposAuditoria)) . ' = ? WHERE id = ?'
        );
        $stmt->execute([...array_values($camposAuditoria), $courseId]);
    }

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
    // true = ficou esperando aprovação; falta chamar api/aprovacao/notificar.php
    'pendente_aprovacao' => $paraAprovacao,
], 201);
