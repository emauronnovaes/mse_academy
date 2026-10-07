<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php';

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
 *   POST {course_id, questions:[...]}    grava o conjunto inteiro
 *   POST {course_id, questions:[]}       tira todas
 *   + confirmar:true                     quando a gravação apagaria respostas
 *
 * Cada item de "questions" é {question, momento_seg, options:[{text,is_correct},...]}.
 * momento_seg é o segundo do vídeo em que a pergunta aparece (atividade
 * durante o vídeo); null ou ausente = no fim do vídeo, como sempre foi.
 *
 * O GET devolve is_correct, que api/courses/detail.php esconde de
 * propósito — lá é o colaborador lendo, e a resposta certa não pode sair
 * junto com a pergunta. Aqui é admin, que precisa ver o que está gravado.
 *
 * O QUE O POST PRESERVA
 * quiz_attempts aponta pra quiz_options com ON DELETE CASCADE: apagar uma
 * pergunta apaga as respostas dela. Antes o POST apagava e recriava tudo,
 * então só marcar o minuto de uma atividade num vídeo que já estava no ar
 * jogava fora o registro de quem respondeu — evidência que a auditoria
 * pede. Agora pergunta que chega igual (mesmo enunciado e mesmas
 * alternativas, na mesma ordem) é mantida: só ordem e momento são
 * atualizados, e as respostas dela continuam. Apaga e recria apenas o que
 * mudou de texto ou saiu da tela — e só pede confirmação se ISSO tiver
 * respostas. Quem já assistiu nunca é afetado: isso vive em
 * user_course_progress, que não é tocado aqui.
 */

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'POST' && $metodo !== 'GET') {
    mse_error('Método não permitido.', 405);
}

// Com atividades no meio do vídeo, 10 ficava curto: um vídeo longo pode
// ter uma pergunta a cada poucos minutos.
const QUIZ_MAX_PERGUNTAS = 50;
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

$temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
$temDuracao = mse_tem_coluna($pdo, 'courses', 'duration_seconds');

$stmt = $pdo->prepare(
    'SELECT id, title, ' . ($temDuracao ? 'duration_seconds' : 'NULL AS duration_seconds') . ' FROM courses WHERE id = ?'
);
$stmt->execute([$courseId]);
$curso = $stmt->fetch();
if (!$curso) {
    mse_error('Aula não encontrada.', 404);
}
$duracao = $curso['duration_seconds'] !== null ? (int) $curso['duration_seconds'] : 0;

// Perguntas que já existem, em ordem, com as alternativas de cada uma.
$stmt = $pdo->prepare(
    'SELECT id, question_text, ' . ($temMomento ? 'momento_seg' : 'NULL AS momento_seg') . '
     FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC, id ASC'
);
$stmt->execute([$courseId]);
$atuais = $stmt->fetchAll();

$stmtOpcoes = $pdo->prepare(
    'SELECT option_text, is_correct FROM quiz_options WHERE question_id = ? ORDER BY order_index ASC, id ASC'
);
$stmtRespostas = $pdo->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE question_id = ?');
foreach ($atuais as &$q) {
    $stmtOpcoes->execute([(int) $q['id']]);
    $q['opcoes'] = array_map(function ($o) {
        return ['text' => $o['option_text'], 'is_correct' => (int) $o['is_correct'] === 1];
    }, $stmtOpcoes->fetchAll());
    $stmtRespostas->execute([(int) $q['id']]);
    $q['respostas'] = (int) $stmtRespostas->fetchColumn();
}
unset($q);

$respostasExistentes = array_sum(array_column($atuais, 'respostas'));

// ------------------------------------------------------------
// GET: o que já está gravado
// ------------------------------------------------------------
if ($metodo === 'GET') {
    mse_json([
        'course_id' => $courseId,
        'titulo' => $curso['title'],
        'duracao_seg' => $duracao ?: null,
        'aceita_momento' => $temMomento,
        'questions' => array_map(function ($q) {
            return [
                'question' => $q['question_text'],
                'momento_seg' => $q['momento_seg'] !== null ? (int) $q['momento_seg'] : null,
                'options' => $q['opcoes'],
            ];
        }, $atuais),
        'respostas' => $respostasExistentes,
        'max_perguntas' => QUIZ_MAX_PERGUNTAS,
    ]);
}

// ------------------------------------------------------------
// POST: valida tudo antes de gravar qualquer coisa
// ------------------------------------------------------------
$recebidas = $input['questions'] ?? null;
if (!is_array($recebidas)) {
    mse_error('Informe "questions" (mande uma lista vazia pra tirar todas).', 422);
}

if (count($recebidas) > QUIZ_MAX_PERGUNTAS) {
    mse_error('São no máximo ' . QUIZ_MAX_PERGUNTAS . ' perguntas por aula.', 422);
}

$perguntas = [];
foreach (array_values($recebidas) as $pos => $q) {
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
        mse_error(
            $certas === 0
                ? 'Marque qual é a alternativa correta da pergunta ' . $numero . '.'
                : 'Marque só uma alternativa como correta na pergunta ' . $numero . '.',
            422
        );
    }

    // Momento da atividade. 0 não vale: no segundo zero o vídeo nem
    // começou, e a pessoa veria a pergunta antes de qualquer conteúdo.
    $momento = null;
    if (isset($q['momento_seg']) && $q['momento_seg'] !== '' && $q['momento_seg'] !== null) {
        if (!$temMomento) {
            mse_error('Atividade com minuto marcado ainda não está disponível: falta rodar a migração 020 no banco.', 409);
        }
        $momento = (int) $q['momento_seg'];
        if ($momento < 1) {
            mse_error('O momento da pergunta ' . $numero . ' precisa ser depois do início do vídeo (a partir de 00:01).', 422);
        }
        if ($duracao > 0 && $momento >= $duracao) {
            mse_error(
                'O momento da pergunta ' . $numero . ' (' . mse_mmss($momento) . ') passa do fim do vídeo ('
                    . mse_mmss($duracao) . '). Deixe em branco pra ela aparecer no fim.',
                422
            );
        }
    }

    $perguntas[] = ['texto' => $texto, 'opcoes' => $opcoes, 'momento' => $momento];
}

function mse_mmss(int $seg): string
{
    return sprintf('%02d:%02d', intdiv($seg, 60), $seg % 60);
}

if (!$perguntas && !$atuais) {
    mse_error('Esta aula não tem pergunta pra tirar.', 409);
}

// Casa cada pergunta recebida com uma já gravada que seja idêntica (texto e
// alternativas). As casadas são mantidas; as que sobram, apagadas.
$assinatura = function (string $texto, array $opcoes): string {
    return json_encode([$texto, array_map(function ($o) {
        return [$o['text'], (bool) $o['is_correct']];
    }, $opcoes)], JSON_UNESCAPED_UNICODE);
};
$livres = [];
foreach ($atuais as $a) {
    $livres[$assinatura($a['question_text'], $a['opcoes'])][] = $a;
}
foreach ($perguntas as &$p) {
    $chave = $assinatura($p['texto'], $p['opcoes']);
    $p['manter_id'] = !empty($livres[$chave]) ? (int) array_shift($livres[$chave])['id'] : null;
}
unset($p);

$apagar = [];
foreach ($livres as $sobra) {
    foreach ($sobra as $a) {
        $apagar[] = $a;
    }
}
$respostasPerdidas = array_sum(array_column($apagar, 'respostas'));

// Confirmação só quando a gravação apaga respostas de verdade — marcar o
// minuto de uma pergunta que não mudou não apaga nada e não pergunta.
if ($respostasPerdidas > 0 && empty($input['confirmar'])) {
    mse_json([
        'precisa_confirmar' => true,
        'course_id' => $courseId,
        'respostas' => $respostasPerdidas,
        'message' => $respostasPerdidas . ' '
            . ($respostasPerdidas === 1 ? 'resposta registrada é' : 'respostas registradas são')
            . ' de perguntas que você alterou ou tirou. Salvar apaga esse registro, e isso não tem volta. '
            . 'As respostas das perguntas que não mudaram continuam. '
            . 'Quem já assistiu a aula continua constando como assistido — isso não é afetado.',
    ]);
}

// ------------------------------------------------------------
// Grava tudo numa transação: ou entra o conjunto inteiro, ou nada.
// ------------------------------------------------------------
$pdo->beginTransaction();
try {
    $del = $pdo->prepare('DELETE FROM quiz_questions WHERE id = ?');
    foreach ($apagar as $a) {
        $del->execute([(int) $a['id']]);
    }

    $insPergunta = $pdo->prepare(
        $temMomento
            ? 'INSERT INTO quiz_questions (course_id, question_text, momento_seg, order_index) VALUES (?, ?, ?, ?)'
            : 'INSERT INTO quiz_questions (course_id, question_text, order_index) VALUES (?, ?, ?)'
    );
    $updPergunta = $pdo->prepare(
        $temMomento
            ? 'UPDATE quiz_questions SET momento_seg = ?, order_index = ? WHERE id = ?'
            : 'UPDATE quiz_questions SET order_index = ? WHERE id = ?'
    );
    $insOpcao = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES (?, ?, ?, ?)');

    foreach ($perguntas as $i => $p) {
        if ($p['manter_id'] !== null) {
            $updPergunta->execute($temMomento ? [$p['momento'], $i + 1, $p['manter_id']] : [$i + 1, $p['manter_id']]);
            continue;
        }
        $insPergunta->execute(
            $temMomento ? [$courseId, $p['texto'], $p['momento'], $i + 1] : [$courseId, $p['texto'], $i + 1]
        );
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
$noMeio = count(array_filter($perguntas, function ($p) { return $p['momento'] !== null; }));
mse_json([
    'course_id' => $courseId,
    'total_perguntas' => $total,
    'message' => $total === 0
        ? 'As perguntas de "' . $curso['title'] . '" foram removidas.'
        : $total . ($total === 1 ? ' pergunta salva' : ' perguntas salvas') . ' em "' . $curso['title'] . '"'
            . ($noMeio > 0 ? ' (' . $noMeio . ' durante o vídeo).' : '.'),
]);
