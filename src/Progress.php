<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Normaliza texto pra comparar cargo/palavra-chave sem se importar com
 * acento, maiúscula/minúscula ou espaço a mais. "Análise Financeira Jr"
 * e "analise   financeira jr" viram a mesma coisa.
 */
function mse_normalize_text(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    // remove acentos (transliteração) — funciona sem precisar da
    // extensão intl, só com iconv (padrão em praticamente todo PHP)
    $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($transliterated !== false) {
        $text = $transliterated;
    }
    $text = preg_replace('/[^a-z0-9\s]/', '', $text) ?? $text;
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

/**
 * true se alguma keyword de cargo associada ao curso aparece dentro do
 * cargo da pessoa (comparação normalizada, "contém", não "é igual a").
 */
function mse_cargo_matches_course(PDO $pdo, string $cargo, int $courseId): bool
{
    if (trim($cargo) === '') {
        return false;
    }
    $normalizedCargo = mse_normalize_text($cargo);

    $stmt = $pdo->prepare('SELECT keyword FROM course_cargo_keywords WHERE course_id = ?');
    $stmt->execute([$courseId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $keyword) {
        if ($keyword !== '' && mse_str_contains($normalizedCargo, $keyword)) {
            return true;
        }
    }
    return false;
}
/**
 * Verifica se o usuário pode assistir/responder este módulo.
 *
 * - Cursos do tipo "curso" (catálogo livre, aba Cursos): sempre liberado.
 * - Cursos do tipo "onboarding" (trilha obrigatória): só libera se TODOS
 *   os módulos anteriores (order_index menor) já estiverem "concluido"
 *   para esse usuário. Essa checagem roda no servidor a cada chamada —
 *   não dá pra burlar mandando o course_id "errado" direto pela API.
 */
/**
 * Resolve quais aulas da trilha ESTA pessoa deve ver.
 *
 * Aula sem grupo_sorteio todo mundo vê. Aulas que compartilham o mesmo
 * grupo_sorteio são alternativas entre si: só uma é sorteada por pessoa,
 * e a escolha fica gravada — sortear a cada acesso faria a pessoa cair
 * num vídeo diferente toda vez, jogando fora o progresso do anterior.
 *
 * @return array<int, array{id:int, obrigatorio:int, order_index:int}>
 */
/**
 * A pessoa é de OBRA (senão, é da sede)? Pela ficha do Portal (users.eh_obra):
 * tem obra e NÃO está alocada na sede. Vale tanto para quem entra pelo CPF
 * quanto por e-mail (tem gente de obra com e-mail).
 */
function mse_usuario_eh_obra(PDO $pdo, int $userId): bool
{
    if (!mse_tem_coluna($pdo, 'users', 'eh_obra')) {
        return false;
    }
    $st = $pdo->prepare('SELECT eh_obra FROM users WHERE id = ?');
    $st->execute([$userId]);
    return (int) $st->fetchColumn() === 1;
}

/**
 * Trecho de WHERE que tira os vídeos que não são para essa pessoa: quem é de
 * obra não vê os "só para sede"; quem é da sede não vê os "só para obra".
 * Vale para integração e cursos. $alias: 'c' -> "c.somente_obra"; '' -> "somente_obra".
 */
function mse_sql_publico(PDO $pdo, bool $ehObra, string $alias = 'c'): string
{
    $col = $alias !== '' ? $alias . '.' : '';
    if ($ehObra) {
        return mse_tem_coluna($pdo, 'courses', 'somente_sede') ? " AND {$col}somente_sede = 0" : '';
    }
    return mse_tem_coluna($pdo, 'courses', 'somente_obra') ? " AND {$col}somente_obra = 0" : '';
}

/**
 * Cursos do catálogo que essa pessoa já tem na trilha de Integração: o mesmo
 * vídeo (mesmo link do YouTube ou mesmo arquivo) está numa aula de integração
 * dela. Esses cursos não aparecem em Cursos para ela (nem contam como
 * pendência), para não ver o mesmo vídeo duas vezes.
 *
 * @return int[]
 */
function mse_cursos_cobertos_pela_trilha(PDO $pdo, int $userId): array
{
    $trilha = array_column(mse_aulas_da_trilha($pdo, $userId), 'id');
    if (!$trilha) {
        return [];
    }
    $st = $pdo->prepare(
        "SELECT DISTINCT c.id FROM courses c
         JOIN courses i ON i.id IN (" . implode(',', array_map('intval', $trilha)) . ") AND " . mse_sql_mesmo_video('c', 'i') . "
         WHERE c.type = 'curso'"
    );
    $st->execute();
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}

/** Condição SQL: as aulas $a e $b usam o mesmo vídeo (mesmo link do YouTube ou mesmo arquivo). */
function mse_sql_mesmo_video(string $a, string $b): string
{
    return "{$b}.video_source = {$a}.video_source
              AND (({$a}.video_source = 'youtube' AND {$a}.youtube_id <> '' AND {$a}.youtube_id = {$b}.youtube_id)
                OR ({$a}.video_source <> 'youtube' AND {$a}.video_key <> '' AND {$a}.video_key = {$b}.video_key))";
}

/**
 * Quem concluiu um curso conta como tendo concluído a aula da integração com o
 * mesmo vídeo (ex.: NR-06 assistida em Cursos antes de ir para a integração da
 * obra), e vice-versa: quem concluiu a NR-06 na integração fica com o curso
 * concluído. Grava a conclusão com a data da outra. Quem não assistiu continua
 * devendo.
 *
 * Sem $userId vale para todo mundo.
 */
function mse_sincronizar_mesmo_video(PDO $pdo, ?int $userId = null): void
{
    $soEla = $userId ? ' AND p.user_id = ' . (int) $userId : '';
    foreach ([['curso', 'onboarding'], ['onboarding', 'curso']] as [$de, $para]) {
        $origem = "SELECT p.user_id, i.id AS course_id, MIN(COALESCE(p.completed_at, p.updated_at)) AS concluido_em
                   FROM user_course_progress p
                   JOIN courses c ON c.id = p.course_id AND c.type = '{$de}'
                   JOIN courses i ON i.type = '{$para}' AND " . mse_sql_mesmo_video('c', 'i') . "
                   WHERE p.status = 'concluido'{$soEla}
                   GROUP BY p.user_id, i.id";
        try {
            // Começou a outra aula (ou foi resetada) mas já tinha concluído esta: conclui.
            $pdo->exec(
                "UPDATE user_course_progress u
                 JOIN ({$origem}) o ON o.user_id = u.user_id AND o.course_id = u.course_id
                 SET u.status = 'concluido', u.watched_pct = 100, u.completed_at = o.concluido_em
                 WHERE u.status <> 'concluido'"
            );
            // Nunca abriu a outra aula: cria a conclusão.
            $pdo->exec(
                "INSERT IGNORE INTO user_course_progress (user_id, course_id, status, watched_pct, completed_at)
                 SELECT o.user_id, o.course_id, 'concluido', 100, o.concluido_em FROM ({$origem}) o"
            );
        } catch (Throwable $e) {
            error_log('[mesmo_video] ' . $e->getMessage());
        }
    }
}

/**
 * Copia o progresso da pessoa nesta aula para a aula da outra trilha com o mesmo
 * vídeo (NR-06/NR-12 da integração <-> NR-06/NR-12 de Cursos). Sem isso, quem
 * assiste pela integração só aparecia na cópia, e o curso ficava sem registro.
 * O % só sobe e "concluído" nunca volta atrás, como no progress/update.php.
 */
function mse_espelhar_progresso_mesmo_video(PDO $pdo, int $userId, int $courseId): void
{
    try {
        $gemeas = mse_aulas_mesmo_video($pdo, $courseId);
        if (!$gemeas) {
            return;
        }
        $temCheckin = mse_tem_coluna($pdo, 'user_course_progress', 'checkin_em');
        $st = $pdo->prepare('SELECT status, watched_pct, completed_at' . ($temCheckin ? ', checkin_em' : ', NULL AS checkin_em')
            . ' FROM user_course_progress WHERE user_id = ? AND course_id = ?');
        $st->execute([$userId, $courseId]);
        $p = $st->fetch();
        if (!$p) {
            return;
        }
        $st = $pdo->prepare(
            "INSERT INTO user_course_progress (user_id, course_id, status, watched_pct, completed_at" . ($temCheckin ? ', checkin_em' : '') . ")
             VALUES (?, ?, ?, ?, ?" . ($temCheckin ? ', ?' : '') . ")
             ON DUPLICATE KEY UPDATE
                watched_pct = GREATEST(watched_pct, VALUES(watched_pct)),
                status = IF(status = 'concluido' OR VALUES(status) = 'concluido', 'concluido',
                         IF(status = 'em_andamento' OR VALUES(status) = 'em_andamento', 'em_andamento', status)),
                completed_at = COALESCE(completed_at, VALUES(completed_at))"
                . ($temCheckin ? ', checkin_em = COALESCE(checkin_em, VALUES(checkin_em))' : '')
        );
        foreach ($gemeas as $outra) {
            $v = [$userId, $outra, $p['status'], $p['watched_pct'], $p['completed_at']];
            if ($temCheckin) {
                $v[] = $p['checkin_em'];
            }
            $st->execute($v);
        }
    } catch (Throwable $e) {
        error_log('[mesmo_video] espelhar: ' . $e->getMessage());
    }
}

/** Aulas da outra trilha (curso <-> integração) com o mesmo vídeo desta. @return int[] */
function mse_aulas_mesmo_video(PDO $pdo, int $courseId): array
{
    $st = $pdo->prepare(
        "SELECT o.id FROM courses c JOIN courses o ON o.id <> c.id AND o.type <> c.type AND " . mse_sql_mesmo_video('c', 'o') . "
         WHERE c.id = ?"
    );
    $st->execute([$courseId]);
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}

function mse_aulas_da_trilha(PDO $pdo, int $userId): array
{
    // O código chega pelo Git antes da migração ser rodada à mão no
    // servidor. Sem esta checagem, a consulta morre com "Unknown column"
    // e a trilha inteira some da tela do colaborador. Enquanto a
    // migração não roda, vale o comportamento antigo: todo mundo vê
    // todas as aulas, todas obrigatórias.
    $colunas = [];
    foreach ($pdo->query('SHOW COLUMNS FROM courses') as $col) {
        $colunas[$col['Field']] = true;
    }
    $temSorteio = isset($colunas['grupo_sorteio'])
        && $pdo->query("SHOW TABLES LIKE 'user_sorteio_aula'")->fetch() !== false;
    $temObrigatorio = isset($colunas['obrigatorio']);
    // Vídeo "só para obra" / "só para sede" (ver mse_usuario_eh_obra).
    $ehObra = mse_usuario_eh_obra($pdo, $userId);

    $stmt = $pdo->query(
        "SELECT id, order_index,
                " . ($temSorteio ? 'grupo_sorteio' : 'NULL AS grupo_sorteio') . ",
                " . ($temObrigatorio ? 'obrigatorio' : '1 AS obrigatorio') . "
         FROM courses
         WHERE type = 'onboarding' AND is_published = 1" . mse_sql_publico($pdo, $ehObra, '') . "
         ORDER BY order_index ASC, id ASC"
    );
    $todas = $stmt->fetchAll();

    $semGrupo = [];
    $porGrupo = [];
    foreach ($todas as $c) {
        if ($c['grupo_sorteio'] === null || $c['grupo_sorteio'] === '') {
            $semGrupo[] = $c;
        } else {
            $porGrupo[$c['grupo_sorteio']][] = $c;
        }
    }

    $escolhidas = $semGrupo;

    foreach ($porGrupo as $grupo => $alternativas) {
        $idsValidos = array_column($alternativas, 'id');

        $stmt = $pdo->prepare('SELECT course_id FROM user_sorteio_aula WHERE user_id = ? AND grupo = ?');
        $stmt->execute([$userId, $grupo]);
        $jaSorteado = $stmt->fetchColumn();

        // Se a aula sorteada foi apagada ou despublicada depois, sorteia
        // de novo em vez de deixar a pessoa sem nada naquele grupo.
        if ($jaSorteado && in_array((int) $jaSorteado, $idsValidos, true)) {
            $escolhidoId = (int) $jaSorteado;
        } else {
            $escolhidoId = $idsValidos[random_int(0, count($idsValidos) - 1)];
            $stmt = $pdo->prepare(
                'INSERT INTO user_sorteio_aula (user_id, grupo, course_id) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE course_id = VALUES(course_id)'
            );
            $stmt->execute([$userId, $grupo, $escolhidoId]);
        }

        foreach ($alternativas as $alt) {
            if ((int) $alt['id'] === $escolhidoId) {
                $escolhidas[] = $alt;
                break;
            }
        }
    }

    usort($escolhidas, static fn($a, $b) => [$a['order_index'], $a['id']] <=> [$b['order_index'], $b['id']]);

    return array_map(static fn($c) => [
        'id' => (int) $c['id'],
        'obrigatorio' => (int) $c['obrigatorio'],
        'order_index' => (int) $c['order_index'],
    ], $escolhidas);
}

/**
 * Integração de várias pessoas de uma vez, pela trilha de cada uma (mesma
 * regra de mse_aulas_da_trilha: "só para obra", sorteio e obrigatórias),
 * sem sortear nada nem fazer uma consulta por pessoa.
 *
 * @return array<int, array{total:int, concluidas:int, concluiu_em:?string}>  por user_id
 *         (concluiu_em: quando terminou a última aula, só se terminou todas)
 */
function mse_integracao_por_pessoa(PDO $pdo): array
{
    mse_sincronizar_mesmo_video($pdo); // curso concluído conta na aula da integração com o mesmo vídeo
    $temSorteio = mse_tem_coluna($pdo, 'courses', 'grupo_sorteio') && mse_tem_tabela($pdo, 'user_sorteio_aula');
    $temObrigatorio = mse_tem_coluna($pdo, 'courses', 'obrigatorio');
    $temObra = mse_tem_coluna($pdo, 'courses', 'somente_obra') && mse_tem_coluna($pdo, 'users', 'eh_obra');
    $temSede = mse_tem_coluna($pdo, 'courses', 'somente_sede') && mse_tem_coluna($pdo, 'users', 'eh_obra');

    $aulas = $pdo->query(
        'SELECT id, ' . ($temSorteio ? 'grupo_sorteio' : 'NULL AS grupo_sorteio') . ', '
        . ($temObra ? 'somente_obra' : '0 AS somente_obra') . ', '
        . ($temSede ? 'somente_sede' : '0 AS somente_sede')
        . " FROM courses WHERE type = 'onboarding' AND is_published = 1"
        . ($temObrigatorio ? ' AND obrigatorio = 1' : '')
    )->fetchAll();

    $ehObra = [];
    if ($temObra || $temSede) {
        foreach ($pdo->query('SELECT id FROM users WHERE eh_obra = 1') as $u) { // mesma regra de mse_usuario_eh_obra
            $ehObra[(int) $u['id']] = true;
        }
    }
    $sorteio = [];
    if ($temSorteio) {
        foreach ($pdo->query('SELECT user_id, grupo, course_id FROM user_sorteio_aula') as $s) {
            $sorteio[(int) $s['user_id']][$s['grupo']] = (int) $s['course_id'];
        }
    }
    $feitas = [];
    foreach ($pdo->query(
        "SELECT p.user_id, p.course_id, p.completed_at FROM user_course_progress p JOIN courses c ON c.id = p.course_id
         WHERE c.type = 'onboarding' AND p.status = 'concluido'"
    ) as $p) {
        $feitas[(int) $p['user_id']][(int) $p['course_id']] = (string) $p['completed_at'];
    }

    $calc = static function (int $uid) use ($aulas, $ehObra, $sorteio, $feitas): array {
        $obra = isset($ehObra[$uid]);
        $total = 0;
        $concluidas = 0;
        $ultima = '';
        $grupos = [];
        foreach ($aulas as $a) {
            if (((int) $a['somente_obra'] === 1 && !$obra) || ((int) $a['somente_sede'] === 1 && $obra)) {
                continue;
            }
            $id = (int) $a['id'];
            $g = $a['grupo_sorteio'];
            if ($g === null || $g === '') {
                $total++;
                if (isset($feitas[$uid][$id])) {
                    $concluidas++;
                    $ultima = max($ultima, $feitas[$uid][$id]);
                }
                continue;
            }
            // Grupo sorteado conta uma aula só: a sorteada (ou, sem sorteio
            // ainda, qualquer alternativa que ela já tenha concluído).
            $grupos[$g][] = $id;
        }
        foreach ($grupos as $g => $ids) {
            $total++;
            $sorteada = $sorteio[$uid][$g] ?? null;
            $alvo = $sorteada !== null && in_array($sorteada, $ids, true) ? [$sorteada] : $ids;
            foreach ($alvo as $id) {
                if (isset($feitas[$uid][$id])) {
                    $concluidas++;
                    $ultima = max($ultima, $feitas[$uid][$id]);
                    break;
                }
            }
        }
        return ['total' => $total, 'concluidas' => $concluidas,
            'concluiu_em' => $total > 0 && $concluidas >= $total && $ultima !== '' ? $ultima : null];
    };

    $r = [];
    foreach ($pdo->query('SELECT id FROM users WHERE active = 1') as $u) {
        $r[(int) $u['id']] = $calc((int) $u['id']);
    }
    return $r;
}

function mse_course_is_unlocked(PDO $pdo, int $userId, int $courseId): bool
{
    $stmt = $pdo->prepare('SELECT type, order_index FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();

    if (!$course) {
        return false;
    }
    if ($course['type'] !== 'onboarding') {
        return true;
    }

    // Só contam as aulas que ESTA pessoa vê, e só as obrigatórias. Sem
    // isso, as alternativas não sorteadas (que ela nunca vai ver) e as
    // opcionais travariam a trilha inteira pra sempre.
    $minhas = mse_aulas_da_trilha($pdo, $userId);
    $previousIds = [];
    foreach ($minhas as $aula) {
        if ($aula['order_index'] < (int) $course['order_index'] && $aula['obrigatorio'] === 1) {
            $previousIds[] = $aula['id'];
        }
    }

    if (empty($previousIds)) {
        return true; // é o primeiro módulo da trilha
    }

    $placeholders = implode(',', array_fill(0, count($previousIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total FROM user_course_progress
         WHERE user_id = ? AND course_id IN ({$placeholders}) AND status = 'concluido'"
    );
    $stmt->execute([$userId, ...$previousIds]);
    $row = $stmt->fetch();

    return (int) $row['total'] === count($previousIds);
}

/**
 * true se a coluna existe. O código chega pelo Git antes de a migração ser
 * rodada à mão no servidor; sem esta checagem a consulta morre com
 * "Unknown column". Guarda o resultado pra não repetir a consulta.
 */
function mse_tem_coluna(PDO $pdo, string $tabela, string $coluna): bool
{
    static $cache = [];
    $chave = $tabela . '.' . $coluna;
    if (!array_key_exists($chave, $cache)) {
        // information_schema e não SHOW COLUMNS ... LIKE ?: com prepared
        // statement de verdade (EMULATE_PREPARES=false) o MySQL não aceita
        // parâmetro no SHOW, e a consulta morria com erro de sintaxe.
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$tabela, $coluna]);
        $cache[$chave] = $stmt->fetchColumn() !== false;
    }
    return $cache[$chave];
}

/**
 * true se a aula tem pergunta pra responder no FIM do vídeo. Pergunta com
 * momento_seg é atividade do meio do vídeo e não conta aqui.
 */
function mse_tem_pergunta_final(PDO $pdo, int $courseId): bool
{
    $filtro = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg') ? ' AND momento_seg IS NULL' : '';
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM quiz_questions WHERE course_id = ?' . $filtro);
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Quantas atividades do meio do vídeo esta pessoa ainda não respondeu (na
 * rodada atual). A aula só conclui com zero — senão bastaria pular a
 * atividade pela API.
 */
function mse_atividades_pendentes(PDO $pdo, int $userId, int $courseId): int
{
    $n = 0;
    foreach (mse_quiz_situacao($pdo, $userId, $courseId)['perguntas'] as $q) {
        if ($q['momento_seg'] !== null && !$q['respondida']) {
            $n++;
        }
    }
    return $n;
}

// ================================================================
// Regra das perguntas (aulas e integração)
// ================================================================
// - Cada pergunta é respondida UMA vez e o vídeo segue, acertando ou errando
//   (errar não volta o vídeo nem trava a pergunta).
// - Só no FIM (depois da última pergunta, ou no fim do vídeo quando a aula
//   só tem atividades no meio) a nota é conferida: a aula só conclui com
//   pelo menos 70% de acerto. Abaixo disso a pessoa refaz SÓ as perguntas
//   que errou: o vídeo volta ao trecho antes de cada uma delas (as certas
//   continuam valendo). Ao responder a última que faltava, a nota é
//   conferida de novo.
// - Rodada = tentativas com id maior que user_course_progress.quiz_rodada_desde.
// - Vale a ÚLTIMA resposta de cada pergunta na rodada. Errada respondida
//   antes de quiz_refazer_desde volta a ficar pendente (precisa refazer).
const MSE_QUIZ_NOTA_MINIMA = 0.70;

/** Cria a coluna se a migração ainda não rodou. Devolve false se não deu. */
function mse_garantir_coluna_quiz(PDO $pdo, string $coluna): bool
{
    static $ok = [];
    if (isset($ok[$coluna])) {
        return $ok[$coluna];
    }
    $stmt = $pdo->prepare(
        "SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_course_progress' AND COLUMN_NAME = ?"
    );
    $stmt->execute([$coluna]);
    if ($stmt->fetchColumn() === false) {
        try {
            $pdo->exec("ALTER TABLE user_course_progress ADD COLUMN {$coluna} BIGINT UNSIGNED NOT NULL DEFAULT 0");
        } catch (Throwable $e) {
            return $ok[$coluna] = false;
        }
    }
    return $ok[$coluna] = true;
}

/** Coluna da rodada (migração 050). */
function mse_garantir_rodada_quiz(PDO $pdo): bool
{
    return mse_garantir_coluna_quiz($pdo, 'quiz_rodada_desde');
}

/** Coluna do "refazer as erradas" (migração 052). */
function mse_garantir_refazer_quiz(PDO $pdo): bool
{
    return mse_garantir_coluna_quiz($pdo, 'quiz_refazer_desde');
}

/** [rodada, refazer]: ids de tentativa que marcam o início da rodada e o último "refazer" (0 = nenhum). */
function mse_quiz_marcos(PDO $pdo, int $userId, int $courseId): array
{
    $rodada = mse_garantir_rodada_quiz($pdo) ? 'quiz_rodada_desde' : '0';
    $refazer = mse_garantir_refazer_quiz($pdo) ? 'quiz_refazer_desde' : '0';
    $stmt = $pdo->prepare("SELECT {$rodada} AS rodada, {$refazer} AS refazer FROM user_course_progress WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $l = $stmt->fetch();
    return $l ? [(int) $l['rodada'], (int) $l['refazer']] : [0, 0];
}

/** Id da última tentativa que NÃO conta mais (0 = todas contam). */
function mse_quiz_rodada_desde(PDO $pdo, int $userId, int $courseId): int
{
    return mse_quiz_marcos($pdo, $userId, $courseId)[0];
}

/**
 * Como estão as perguntas da aula nesta rodada. "valendo" é a última
 * resposta (true certa, false errada, null sem resposta); errada dada antes
 * do último "refazer" fica pendente até ser respondida de novo.
 *
 * @return array{perguntas: array<int, array{id:int, momento_seg:?int, valendo:?bool, respondida:bool, seg_desde_erro:?int}>,
 *               total:int, acertos_primeira:int, erros_primeira:int, pendentes:int, refazendo:bool}
 */
function mse_quiz_situacao(PDO $pdo, int $userId, int $courseId): array
{
    [$desde, $refazer] = mse_quiz_marcos($pdo, $userId, $courseId);
    $momento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg') ? 'q.momento_seg' : 'NULL';
    $stmt = $pdo->prepare(
        "SELECT q.id, $momento AS momento_seg,
                (SELECT t.is_correct FROM quiz_attempts t
                  WHERE t.question_id = q.id AND t.user_id = :u1 AND t.id > :d1 ORDER BY t.id DESC LIMIT 1) AS valendo,
                (SELECT MAX(t.id) FROM quiz_attempts t
                  WHERE t.question_id = q.id AND t.user_id = :u2 AND t.id > :d2) AS ultima_id,
                (SELECT TIMESTAMPDIFF(SECOND, MAX(t.attempted_at), NOW()) FROM quiz_attempts t
                  WHERE t.question_id = q.id AND t.user_id = :u3 AND t.id > :d3 AND t.is_correct = 0) AS seg_desde_erro
         FROM quiz_questions q
         WHERE q.course_id = :c
         ORDER BY q.id"
    );
    $stmt->execute(['u1' => $userId, 'u2' => $userId, 'u3' => $userId, 'd1' => $desde, 'd2' => $desde, 'd3' => $desde, 'c' => $courseId]);

    $perguntas = [];
    $acertos = 0;
    $erros = 0;
    $pendentes = 0;
    foreach ($stmt->fetchAll() as $l) {
        $valendo = $l['valendo'] === null ? null : (bool) $l['valendo'];
        $p = [
            'id' => (int) $l['id'],
            'momento_seg' => $l['momento_seg'] === null ? null : (int) $l['momento_seg'],
            'valendo' => $valendo,
            // Errada de antes do "refazer": precisa responder de novo.
            'respondida' => $valendo !== null && !($valendo === false && (int) $l['ultima_id'] <= $refazer),
            'seg_desde_erro' => $l['seg_desde_erro'] === null ? null : (int) $l['seg_desde_erro'],
        ];
        if ($p['respondida']) {
            if ($valendo) {
                $acertos++;
            } else {
                $erros++;
            }
        } else {
            $pendentes++;
        }
        $perguntas[$p['id']] = $p;
    }
    return [
        'perguntas' => $perguntas,
        'total' => count($perguntas),
        'acertos_primeira' => $acertos,
        'erros_primeira' => $erros,
        'pendentes' => $pendentes,
        // Refazendo as erradas: a nota fecha quando responde a última que faltava,
        // sem precisar chegar ao fim do vídeo.
        'refazendo' => $refazer > $desde,
    ];
}

/** true se a nota da rodada (última resposta de cada pergunta) chega nos 70% (aula sem pergunta: sempre). */
function mse_quiz_aprovado(array $situacao): bool
{
    return $situacao['total'] === 0
        || $situacao['acertos_primeira'] / $situacao['total'] >= MSE_QUIZ_NOTA_MINIMA - 1e-9;
}

/** true se, com os erros de primeira que já tem, não dá mais pra chegar nos 70%. */
function mse_quiz_sem_chance(array $situacao): bool
{
    return $situacao['total'] > 0
        && ($situacao['total'] - $situacao['erros_primeira']) / $situacao['total'] < MSE_QUIZ_NOTA_MINIMA - 1e-9;
}

/**
 * Trecho que precisa ser assistido de novo pra responder outra vez a
 * pergunta: da atividade anterior (ou do início) até o momento dela; a
 * pergunta do fim vai da última atividade até o fim do vídeo.
 *
 * @return array{de:int, ate:?int} ate null = fim do vídeo
 */
function mse_quiz_trecho(array $situacao, int $questionId): array
{
    $alvo = $situacao['perguntas'][$questionId]['momento_seg'] ?? null;
    $de = 0;
    foreach ($situacao['perguntas'] as $q) {
        $m = $q['momento_seg'];
        if ($m !== null && ($alvo === null || $m < $alvo) && $m > $de) {
            $de = $m;
        }
    }
    return ['de' => $de, 'ate' => $alvo];
}

/**
 * Trechos a assistir de novo para refazer as perguntas pendentes que estavam
 * erradas, na ordem do vídeo (a do fim por último).
 *
 * @return list<array{question_id:int, de:int, ate:?int}>
 */
function mse_quiz_trechos_refazer(array $situacao): array
{
    $trechos = [];
    foreach ($situacao['perguntas'] as $q) {
        if ($q['valendo'] === false && !$q['respondida']) {
            $trechos[] = ['question_id' => $q['id']] + mse_quiz_trecho($situacao, $q['id']);
        }
    }
    usort($trechos, static fn($a, $b) => ($a['ate'] ?? PHP_INT_MAX) <=> ($b['ate'] ?? PHP_INT_MAX));
    return $trechos;
}

/**
 * Reprovou (abaixo de 70%): as erradas voltam a ficar pendentes e o %
 * assistido volta ao trecho da primeira delas. As certas continuam valendo.
 * Devolve o segundo em que o vídeo recomeça.
 */
function mse_quiz_refazer_erradas(PDO $pdo, int $userId, int $courseId): int
{
    if (!mse_garantir_refazer_quiz($pdo)) {
        // Sem a coluna nova: o jeito antigo, a aula recomeça do início.
        mse_quiz_reiniciar_rodada($pdo, $userId, $courseId);
        return 0;
    }
    $ultimo = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM quiz_attempts')->fetchColumn();
    $stmt = $pdo->prepare(
        "UPDATE user_course_progress SET quiz_refazer_desde = ?
         WHERE user_id = ? AND course_id = ? AND status <> 'concluido'"
    );
    $stmt->execute([$ultimo, $userId, $courseId]);

    $trechos = mse_quiz_trechos_refazer(mse_quiz_situacao($pdo, $userId, $courseId));
    $voltar = $trechos ? $trechos[0]['de'] : 0;
    $duracao = 0;
    if (mse_tem_coluna($pdo, 'courses', 'duration_seconds')) {
        $st = $pdo->prepare('SELECT duration_seconds FROM courses WHERE id = ?');
        $st->execute([$courseId]);
        $duracao = (int) $st->fetchColumn();
    }
    $pct = $duracao > 0 ? max(0, min(100, (int) floor($voltar / $duracao * 100))) : 0;
    $stmt = $pdo->prepare(
        "UPDATE user_course_progress SET watched_pct = ?, status = 'em_andamento'
         WHERE user_id = ? AND course_id = ? AND status <> 'concluido'"
    );
    $stmt->execute([$pct, $userId, $courseId]);
    return $voltar;
}

/** O jeito antigo de reprovar: a aula recomeça do início (nenhuma resposta conta mais). */
function mse_quiz_reiniciar_rodada(PDO $pdo, int $userId, int $courseId): void
{
    if (!mse_garantir_rodada_quiz($pdo)) {
        return;
    }
    $ultimo = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM quiz_attempts')->fetchColumn();
    $stmt = $pdo->prepare(
        "UPDATE user_course_progress
         SET quiz_rodada_desde = ?, watched_pct = 0, status = 'em_andamento'
         WHERE user_id = ? AND course_id = ? AND status <> 'concluido'"
    );
    $stmt->execute([$ultimo, $userId, $courseId]);
}

/** true se a tabela existe (mesma precaução de mse_tem_coluna, pra migração pendente). */
function mse_tem_tabela(PDO $pdo, string $tabela): bool
{
    static $cache = [];
    if (!array_key_exists($tabela, $cache)) {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$tabela]);
        $cache[$tabela] = $stmt->fetchColumn() !== false;
    }
    return $cache[$tabela];
}

/**
 * Grava um evento no log de presença (migração 021): checkin, checkout ou
 * presenca ("Estou aqui"). O % assistido sai do progresso gravado, não do
 * navegador. Sem a tabela, não faz nada — o vídeo segue normalmente.
 *
 * O mesmo evento repetido em menos de 5s é ignorado: recarregar a página
 * ou um clique duplo não vira dois check-ins.
 */
function mse_registrar_evento(PDO $pdo, int $userId, int $courseId, string $evento): bool
{
    if (!in_array($evento, ['checkin', 'checkout', 'presenca'], true) || !mse_tem_tabela($pdo, 'presenca_log')) {
        return false;
    }
    $stmt = $pdo->prepare(
        'SELECT 1 FROM presenca_log
         WHERE user_id = ? AND course_id = ? AND evento = ? AND criado_em >= NOW() - INTERVAL 5 SECOND'
    );
    $stmt->execute([$userId, $courseId, $evento]);
    if ($stmt->fetchColumn() !== false) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT watched_pct FROM user_course_progress WHERE user_id = ? AND course_id = ?');
    $stmt->execute([$userId, $courseId]);
    $pct = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'INSERT INTO presenca_log (user_id, course_id, evento, watched_pct, user_agent) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId, $courseId, $evento,
        $pct === false ? null : $pct,
        mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
    ]);

    // Entrada e saída de vídeo também vão para o log de consulta (consultasmia_2),
    // com a MESMA hora deste registro. Nunca atrapalha o registro principal.
    if ($evento === 'checkin' || $evento === 'checkout') {
        try {
            $reg = $pdo->prepare('SELECT p.criado_em, p.watched_pct, c.title FROM presenca_log p JOIN courses c ON c.id = p.course_id WHERE p.id = ?');
            $reg->execute([(int) $pdo->lastInsertId()]);
            if ($r = $reg->fetch()) {
                require_once __DIR__ . '/Viniconsultas.php';
                mse_vini_registrar_evento(
                    $userId, $evento === 'checkin' ? 'video_entrada' : 'video_saida',
                    ['origem' => 'video', 'percentual_assistido' => $r['watched_pct'] !== null ? (float) $r['watched_pct'] : null],
                    $courseId, (string) $r['title'], (string) $r['criado_em']
                );
            }
        } catch (Throwable $e) {
            error_log('[consultasmia] espelho do vídeo: ' . $e->getMessage());
        }
    }
    return true;
}
