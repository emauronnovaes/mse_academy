<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';

mse_cors();
mse_require_admin();

/**
 * Cria, troca ou tira a pergunta de uma aula depois que ela já existe.
 *
 * Antes a pergunta só podia ser escrita no momento de cadastrar o vídeo.
 * Quem subisse a aula sem pergunta — ou percebesse depois que a pergunta
 * estava ruim — não tinha caminho nenhum: teria que apagar a aula e subir
 * o vídeo de novo, perdendo junto o registro de quem já assistiu.
 *
 * Uma pergunta por aula, que é como o resto do sistema já trata: o
 * cadastro grava uma, a tela mostra uma, e a conta do baú é de uma por
 * módulo. Salvar de novo substitui a que existe.
 *
 * MODO DE USO
 *   GET  ?course_id=N                                            lê a pergunta atual
 *   POST {course_id, question, options:[{text,is_correct},...]}  cria ou troca
 *   POST {course_id, remover:true}                               tira a pergunta
 *   + confirmar:true                                             quando já houve respostas
 *
 * O GET devolve is_correct, que api/courses/detail.php esconde de
 * propósito — lá é o colaborador lendo, e a resposta certa não pode sair
 * junto com a pergunta. Aqui é admin, que precisa ver o que já está
 * gravado pra poder corrigir.
 *
 * O PORQUÊ DA CONFIRMAÇÃO
 * quiz_attempts aponta pra quiz_options com ON DELETE CASCADE. Trocar as
 * opções apaga as respostas que apontavam pra elas — o histórico de quem
 * respondeu o quê some junto, sem aviso. Quem já assistiu NÃO é afetado:
 * isso vive em user_course_progress, que não é tocado aqui.
 */

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'POST' && $metodo !== 'GET') {
    mse_error('Método não permitido.', 405);
}

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

// Pergunta atual, se houver.
$stmt = $pdo->prepare('SELECT id, question_text FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC LIMIT 1');
$stmt->execute([$courseId]);
$atual = $stmt->fetch();

$respostasExistentes = 0;
if ($atual) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE question_id = ?');
    $stmt->execute([(int) $atual['id']]);
    $respostasExistentes = (int) $stmt->fetchColumn();
}

// ------------------------------------------------------------
// GET: o que já está gravado
// ------------------------------------------------------------
if ($metodo === 'GET') {
    $opcoesAtuais = [];
    if ($atual) {
        $stmt = $pdo->prepare(
            'SELECT option_text, is_correct FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC'
        );
        $stmt->execute([(int) $atual['id']]);
        foreach ($stmt->fetchAll() as $o) {
            $opcoesAtuais[] = ['text' => $o['option_text'], 'is_correct' => (int) $o['is_correct'] === 1];
        }
    }

    mse_json([
        'course_id' => $courseId,
        'titulo' => $curso['title'],
        'tem_pergunta' => (bool) $atual,
        'question' => $atual ? $atual['question_text'] : '',
        'options' => $opcoesAtuais,
        'respostas' => $respostasExistentes,
    ]);
}

$remover = !empty($input['remover']);

// ------------------------------------------------------------
// Validação do que vai ser gravado (antes de qualquer escrita)
// ------------------------------------------------------------
$pergunta = '';
$opcoes = [];

if (!$remover) {
    $pergunta = trim((string) ($input['question'] ?? ''));
    if ($pergunta === '') {
        mse_error('Escreva a pergunta.', 422);
    }
    if (mb_strlen($pergunta) > 500) {
        mse_error('A pergunta passou de 500 caracteres.', 422);
    }

    foreach ((array) ($input['options'] ?? []) as $o) {
        $texto = trim((string) ($o['text'] ?? ''));
        if ($texto === '') {
            continue; // opção em branco é opção não preenchida, não erro
        }
        if (mb_strlen($texto) > 300) {
            mse_error('Uma das opções passou de 300 caracteres.', 422);
        }
        $opcoes[] = ['text' => $texto, 'is_correct' => !empty($o['is_correct'])];
    }

    if (count($opcoes) < 2) {
        mse_error('A pergunta precisa de pelo menos duas opções preenchidas.', 422);
    }

    $certas = count(array_filter($opcoes, function ($o) { return $o['is_correct']; }));
    if ($certas !== 1) {
        // Nenhuma certa deixaria a pergunta impossível; mais de uma faria o
        // servidor aceitar respostas diferentes como corretas.
        mse_error(
            $certas === 0
                ? 'Marque qual é a opção correta.'
                : 'Marque só uma opção como correta.',
            422
        );
    }
}

if ($remover && !$atual) {
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
            . ($respostasExistentes === 1 ? 'pessoa já respondeu' : 'pessoas já responderam')
            . ' esta pergunta. '
            . ($remover ? 'Tirar' : 'Trocar')
            . ' apaga esse registro de respostas, e isso não tem volta. '
            . 'Quem já assistiu a aula continua constando como assistido — isso não é afetado.',
    ]);
}

// ------------------------------------------------------------
// Passo 2: grava
// ------------------------------------------------------------
$pdo->beginTransaction();
try {
    // Apagar a pergunta leva as opções e as respostas junto, por cascata.
    // É o caminho para os dois casos: tirar de vez, ou recriar do zero.
    if ($atual) {
        $stmt = $pdo->prepare('DELETE FROM quiz_questions WHERE id = ?');
        $stmt->execute([(int) $atual['id']]);
    }

    $questionId = null;
    if (!$remover) {
        $stmt = $pdo->prepare('INSERT INTO quiz_questions (course_id, question_text, order_index) VALUES (?, ?, 1)');
        $stmt->execute([$courseId, $pergunta]);
        $questionId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES (?, ?, ?, ?)');
        foreach ($opcoes as $i => $o) {
            $stmt->execute([$questionId, $o['text'], $o['is_correct'] ? 1 : 0, $i + 1]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Falha ao salvar. Nada foi gravado (a transação desfez tudo). Detalhe: ' . $e->getMessage(), 500);
}

mse_json([
    'course_id' => $courseId,
    'question_id' => $questionId,
    'tem_pergunta' => !$remover,
    'message' => $remover
        ? 'Pergunta removida de "' . $curso['title'] . '".'
        : ($atual
            ? 'Pergunta de "' . $curso['title'] . '" atualizada.'
            : 'Pergunta adicionada a "' . $curso['title'] . '".'),
]);
