<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

/**
 * As perguntas de uma aula, depois que ela já existe.
 *
 * Antes a pergunta só podia ser escrita no momento de cadastrar o vídeo, e
 * só uma. Quem subisse a aula sem pergunta — ou quisesse uma segunda —
 * teria que apagar a aula e subir o vídeo de novo, perdendo junto o
 * registro de quem já assistiu.
 *
 * MODO DE USO
 *   GET  ?course_id=N                    lê as perguntas atuais
 *   POST {course_id, questions:[...]}    substitui o conjunto inteiro
 *   POST {course_id, questions:[]}       tira todas
 *   + confirmar:true                     quando já houve respostas
 *
 * Cada item de "questions" é {question, options:[{text,is_correct},...]}.
 *
 * O POST substitui tudo em vez de mexer pergunta a pergunta. A tela edita
 * o conjunto inteiro de uma vez, e casar edição parcial com o que sumiu da
 * tela exigiria mandar ids e tratar pergunta removida, criada e alterada
 * em três caminhos diferentes — mais código pra chegar no mesmo lugar.
 *
 * O GET devolve is_correct, que api/courses/detail.php esconde de
 * propósito — lá é o colaborador lendo, e a resposta certa não pode sair
 * junto com a pergunta. Aqui é admin, que precisa ver o que está gravado.
 *
 * O PORQUÊ DA CONFIRMAÇÃO
 * quiz_attempts aponta pra quiz_options com ON DELETE CASCADE. Regravar as
 * perguntas apaga as respostas que apontavam pras opções antigas — o
 * registro de quem respondeu o quê some junto, sem aviso. Quem já assistiu
 * NÃO é afetado: isso vive em user_course_progress, que não é tocado aqui.
 */

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'POST' && $metodo !== 'GET') {
    mse_error('Método não permitido.', 405);
}

const QUIZ_MAX_PERGUNTAS = 10;
const QUIZ_MIN_OPCOES = 2;
const QUIZ_MAX_OPCOES = 6;

$pdo = mse_db();
$input = $metodo === 'POST' ? mse_input() : [];

$courseId = $metodo === 'GET'
    ? (int) ($_GET['course_id'] ?? 0)
    : (int) ($input['course_id'] ?? 0);

if ($courseId <= 0) {
    mse_error('Informe course_id.', 422);
}

$stmt = $pdo->prepare('SELECT id, title FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$curso = $stmt->fetch();
if (!$curso) {
    mse_error('Aula não encontrada.', 404);
}

// Perguntas que já existem, em ordem.
$stmt = $pdo->prepare('SELECT id, question_text FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC, id ASC');
$stmt->execute([$courseId]);
$atuais = $stmt->fetchAll();

$respostasExistentes = 0;
if ($atuais) {
    $ids = array_map(function ($q) { return (int) $q['id']; }, $atuais);
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE question_id IN ({$marcas})");
    $stmt->execute($ids);
    $respostasExistentes = (int) $stmt->fetchColumn();
}

// ------------------------------------------------------------
// GET: o que já está gravado
// ------------------------------------------------------------
if ($metodo === 'GET') {
    $lista = [];
    foreach ($atuais as $q) {
        $stmt = $pdo->prepare(
            'SELECT option_text, is_correct FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC, id ASC'
        );
        $stmt->execute([(int) $q['id']]);
        $opcoes = [];
        foreach ($stmt->fetchAll() as $o) {
            $opcoes[] = ['text' => $o['option_text'], 'is_correct' => (int) $o['is_correct'] === 1];
        }
        $lista[] = ['question' => $q['question_text'], 'options' => $opcoes];
    }

    mse_json([
        'course_id' => $courseId,
        'titulo' => $curso['title'],
        'questions' => $lista,
        'respostas' => $respostasExistentes,
        'max_perguntas' => QUIZ_MAX_PERGUNTAS,
    ]);
}

// ------------------------------------------------------------
// Validação de tudo, antes de qualquer escrita
// ------------------------------------------------------------
$recebidas = $input['questions'] ?? null;
if (!is_array($recebidas)) {
    mse_error('Informe "questions" (mande uma lista vazia pra tirar todas).', 422);
}

if (count($recebidas) > QUIZ_MAX_PERGUNTAS) {
    mse_error('São no máximo ' . QUIZ_MAX_PERGUNTAS . ' perguntas por aula.', 422);
}

$perguntas = [];
foreach ($recebidas as $pos => $q) {
    $numero = $pos + 1;
    $texto = trim((string) ($q['question'] ?? ''));

    // Pergunta em branco é linha não preenchida, não erro — igual às
    // opções. Mas só quando ela está vazia por inteiro: uma pergunta sem
    // texto mas com alternativas escritas é engano, não desistência.
    $opcoesBrutas = (array) ($q['options'] ?? []);
    $temAlgumaOpcao = false;
    foreach ($opcoesBrutas as $o) {
        if (trim((string) ($o['text'] ?? '')) !== '') { $temAlgumaOpcao = true; break; }
    }
    if ($texto === '' && !$temAlgumaOpcao) {
        continue;
    }
    if ($texto === '') {
        mse_error('A pergunta ' . $numero . ' está sem enunciado, mas tem alternativas escritas.', 422);
    }
    if (mb_strlen($texto) > 500) {
        mse_error('A pergunta ' . $numero . ' passou de 500 caracteres.', 422);
    }

    $opcoes = [];
    foreach ($opcoesBrutas as $o) {
        $t = trim((string) ($o['text'] ?? ''));
        if ($t === '') {
            continue;
        }
        if (mb_strlen($t) > 300) {
            mse_error('Uma das alternativas da pergunta ' . $numero . ' passou de 300 caracteres.', 422);
        }
        $opcoes[] = ['text' => $t, 'is_correct' => !empty($o['is_correct'])];
    }

    if (count($opcoes) < QUIZ_MIN_OPCOES) {
        mse_error('A pergunta ' . $numero . ' precisa de pelo menos ' . QUIZ_MIN_OPCOES . ' alternativas preenchidas.', 422);
    }
    if (count($opcoes) > QUIZ_MAX_OPCOES) {
        mse_error('A pergunta ' . $numero . ' passou de ' . QUIZ_MAX_OPCOES . ' alternativas.', 422);
    }

    $certas = count(array_filter($opcoes, function ($o) { return $o['is_correct']; }));
    if ($certas !== 1) {
        // Nenhuma certa deixaria a pergunta impossível; mais de uma faria o
        // servidor aceitar respostas diferentes como corretas.
        mse_error(
            $certas === 0
                ? 'Marque qual é a alternativa correta da pergunta ' . $numero . '.'
                : 'Marque só uma alternativa como correta na pergunta ' . $numero . '.',
            422
        );
    }

    $perguntas[] = ['texto' => $texto, 'opcoes' => $opcoes];
}

if (!$perguntas && !$atuais) {
    mse_error('Esta aula não tem pergunta pra tirar.', 409);
}

// ------------------------------------------------------------
// Passo 1: avisa antes de mexer no que já foi respondido
// ------------------------------------------------------------
if ($respostasExistentes > 0 && empty($input['confirmar'])) {
    mse_json([
        'precisa_confirmar' => true,
        'course_id' => $courseId,
        'respostas' => $respostasExistentes,
        'message' => $respostasExistentes . ' '
            . ($respostasExistentes === 1 ? 'resposta já foi registrada' : 'respostas já foram registradas')
            . ' nesta aula. Salvar apaga esse registro de respostas, e isso não tem volta. '
            . 'Quem já assistiu a aula continua constando como assistido — isso não é afetado.',
    ]);
}

// ------------------------------------------------------------
// Passo 2: grava
// ------------------------------------------------------------
$pdo->beginTransaction();
try {
    // Apagar as perguntas leva opções e respostas junto, por cascata. É o
    // caminho para os dois casos: tirar de vez, ou recriar o conjunto.
    $stmt = $pdo->prepare('DELETE FROM quiz_questions WHERE course_id = ?');
    $stmt->execute([$courseId]);

    $insPergunta = $pdo->prepare('INSERT INTO quiz_questions (course_id, question_text, order_index) VALUES (?, ?, ?)');
    $insOpcao = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES (?, ?, ?, ?)');

    foreach ($perguntas as $i => $p) {
        $insPergunta->execute([$courseId, $p['texto'], $i + 1]);
        $questionId = (int) $pdo->lastInsertId();
        foreach ($p['opcoes'] as $j => $o) {
            $insOpcao->execute([$questionId, $o['text'], $o['is_correct'] ? 1 : 0, $j + 1]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Falha ao salvar. Nada foi gravado (a transação desfez tudo). Detalhe: ' . $e->getMessage(), 500);
}

$total = count($perguntas);
mse_json([
    'course_id' => $courseId,
    'total_perguntas' => $total,
    'message' => $total === 0
        ? 'As perguntas de "' . $curso['title'] . '" foram removidas.'
        : $total . ($total === 1 ? ' pergunta salva' : ' perguntas salvas') . ' em "' . $curso['title'] . '".',
]);
