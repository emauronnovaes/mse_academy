<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Progress.php';

mse_cors();
$user = mse_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$questionId = (int) ($input['question_id'] ?? 0);
$optionId = (int) ($input['option_id'] ?? 0);

if ($questionId <= 0 || $optionId <= 0) {
    mse_error('question_id e option_id são obrigatórios.', 422);
}

$pdo = mse_db();

// Confirma que a opção realmente pertence à pergunta enviada
// (evita alguém forjar um option_id de outra pergunta pra "acertar" fácil).
$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$stmt = $pdo->prepare(
    'SELECT o.id, o.is_correct, q.course_id, ' . ($temMomento ? 'q.momento_seg' : 'NULL AS momento_seg') . '
     FROM quiz_options o
     JOIN quiz_questions q ON q.id = o.question_id
     WHERE o.id = ? AND o.question_id = ?'
);
$stmt->execute([$optionId, $questionId]);
$option = $stmt->fetch();

if (!$option) {
    mse_error('Pergunta ou opção inválida.', 422);
}

$courseId = (int) $option['course_id'];

if (!mse_course_is_unlocked($pdo, (int) $user['id'], $courseId)) {
    mse_error('Este módulo ainda está bloqueado.', 403);
}

// Pergunta do fim: exige ter assistido o vídeo (>=95%) antes de aceitar
// qualquer resposta — mesma regra usada no player, reforçada no servidor.
//
// Atividade do meio do vídeo (momento_seg): exige ter chegado àquele
// ponto. O progresso é gravado de 5 em 5%, então a folga é de 6 pontos —
// sem ela, quem está exatamente no momento da atividade seria recusado.
$momento = $option['momento_seg'] === null ? null : (int) $option['momento_seg'];

$stmt = $pdo->prepare(
    'SELECT watched_pct FROM user_course_progress WHERE user_id = ? AND course_id = ?'
);
$stmt->execute([$user['id'], $courseId]);
$progress = $stmt->fetch();
$pctAssistido = $progress ? (float) $progress['watched_pct'] : 0.0;

if ($momento === null) {
    if (!$progress || $pctAssistido < 95) {
        mse_error('Assista o vídeo até o fim antes de responder.', 403);
    }
} else {
    if (!$progress) {
        mse_error('Abra o vídeo antes de responder.', 403);
    }
    $duracao = 0;
    if (mse_tem_coluna($pdo, 'courses', 'duration_seconds')) {
        $stmt = $pdo->prepare('SELECT duration_seconds FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);
        $duracao = (int) $stmt->fetchColumn();
    }
    // Sem duração conhecida não dá pra converter segundo em %, e aí vale
    // só ter aberto o vídeo — o player já não deixa adiantar a barra.
    if ($duracao > 0 && $pctAssistido + 6 < ($momento / $duracao) * 100) {
        mse_error('Assista até esse ponto do vídeo antes de responder.', 403);
    }
}

$isCorrect = (bool) $option['is_correct'];
$uid = (int) $user['id'];

// Regra das perguntas (ver mse_quiz_situacao em Progress.php): uma resposta
// por pergunta, acertando ou errando — errar não volta o vídeo. A nota só é
// conferida no FIM: com 70% de acerto a aula conclui, abaixo disso recomeça.
// Aula já concluída (revisão) fica de fora — ali nada muda mais.
$stmt = $pdo->prepare('SELECT status FROM user_course_progress WHERE user_id = ? AND course_id = ?');
$stmt->execute([$uid, $courseId]);
$jaConcluida = $stmt->fetchColumn() === 'concluido';

$situacao = mse_quiz_situacao($pdo, $uid, $courseId);
$estaPergunta = $situacao['perguntas'][$questionId] ?? null;

/** Resposta padrão, com a nota da rodada pra tela mostrar (mse_json encerra o script). */
$responder = function (array $extra) use (&$situacao): void {
    $total = $situacao['total'];
    mse_json($extra + [
        'acertos' => $situacao['acertos_primeira'],
        'erros' => $situacao['erros_primeira'],
        'total' => $total,
        'pct' => $total ? (int) floor($situacao['acertos_primeira'] / $total * 100) : 100,
        'nota_minima' => (int) round(MSE_QUIZ_NOTA_MINIMA * 100),
    ]);
};

// Já respondida nesta rodada: não grava de novo nem muda a nota (vale a
// primeira resposta). O resultado devolvido é o da opção escolhida agora.
// Segue adiante sem gravar, pra conferir o fim caso a última resposta
// tenha ficado sem o fechamento (ex.: queda de conexão).
$jaRespondida = !$jaConcluida && $estaPergunta && $estaPergunta['respondida'];

if (!$jaRespondida) {
    $stmt = $pdo->prepare(
        'INSERT INTO quiz_attempts (user_id, question_id, option_id, is_correct) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$uid, $questionId, $optionId, $isCorrect ? 1 : 0]);
}

// Fim das perguntas: não falta nenhuma e, se esta é atividade do meio, o
// vídeo já chegou ao fim (aula sem pergunta do fim; senão falta a do fim e
// "pendentes" não zera). Atividade do meio com o vídeo ainda rolando só
// registra a resposta: a nota é conferida no fim do vídeo (progress/update.php).
$concluiAula = false;
if ($jaConcluida) {
    $concluiAula = $isCorrect && $momento === null;
} else {
    if (!$jaRespondida) {
        $situacao = mse_quiz_situacao($pdo, $uid, $courseId);
    }
    $fim = $situacao['pendentes'] === 0 && ($momento === null || $pctAssistido >= 95);
    if ($fim) {
        if (!mse_quiz_aprovado($situacao)) {
            // Abaixo de 70%: a aula recomeça do início.
            mse_quiz_reiniciar_rodada($pdo, $uid, $courseId);
            $responder(['correct' => $isCorrect, 'concluido' => false, 'reprovado' => true]);
        }
        $concluiAula = true;
    }
}

if ($concluiAula) {
    // Confere se ESSE curso já estava concluído antes dessa resposta —
    // sem isso, responder a mesma pergunta certa várias vezes (ex:
    // clicando rápido, ou reassistindo e respondendo de novo) dava
    // pontos e "módulo concluído" de novo a cada vez, sem limite.
    // Testado: 3 respostas certas seguidas na mesma pergunta chegavam
    // a inflar total_points=30 e modules_completed=3 (deveria ser
    // 10 e 1). Só soma pontos na PRIMEIRA vez que o curso é concluído.
    $stmt = $pdo->prepare(
        'SELECT status FROM user_course_progress WHERE user_id = ? AND course_id = ?'
    );
    $stmt->execute([$user['id'], $courseId]);
    $currentProgress = $stmt->fetch();
    $alreadyCompleted = $currentProgress && $currentProgress['status'] === 'concluido';

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "UPDATE user_course_progress
             SET status = 'concluido', completed_at = COALESCE(completed_at, NOW())
             WHERE user_id = ? AND course_id = ?"
        );
        $stmt->execute([$user['id'], $courseId]);

        if (!$alreadyCompleted) {
            $points = 10; // pontuação fixa por módulo — ajuste como quiser
            $stmt = $pdo->prepare(
                'INSERT INTO user_stats (user_id, total_points, modules_completed)
                 VALUES (?, ?, 1)
                 ON DUPLICATE KEY UPDATE
                    total_points = total_points + VALUES(total_points),
                    modules_completed = modules_completed + 1'
            );
            $stmt->execute([$user['id'], $points]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        mse_error('Erro ao registrar conclusão do módulo.', 500);
    }
}

// Concluiu a aula: o banco de consulta (consultasmia) já reflete na hora.
if ($concluiAula) {
    require_once __DIR__ . '/../../src/Viniconsultas.php';
    mse_vini_atualizar_aulas_do_usuario((int) $user['id']);
}

$responder(['correct' => $isCorrect, 'concluido' => $concluiAula]);
