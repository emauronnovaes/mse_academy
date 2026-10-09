<?php
declare(strict_types=1);

require_once __DIR__ . '/Aprovacao.php';
require_once __DIR__ . '/Departamentos.php';

/**
 * Layout do e-mail "vídeo para aprovação".
 *
 * HTML de e-mail é diferente de página: só tabelas e estilo inline (o
 * Outlook ignora CSS em <style>, flex, grid e margem em muitos lugares),
 * largura máxima de 600px e cores sólidas. Por isso tudo aqui é tabela.
 *
 * $d = [
 *   'titulo', 'descricao', 'onde', 'departamentos' (string[]), 'origem', 'duracao_min',
 *   'perguntas' => [['texto', 'momento_seg' (int|null)], ...],
 *   'autor' => ['nome', 'email', 'cargo'], 'enviado_em' (Y-m-d H:i:s), 'link'
 * ]
 */
function mse_email_aprovacao_html(array $d): string
{
    $e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $vermelho = '#C4212C';
    $tinta = '#1C1B1A';
    $cinza = '#5C6470';
    $linha = '#E7E4DE';
    $fonte = "font-family:'Segoe UI',Arial,Helvetica,sans-serif;";

    $autor = $d['autor'] + ['nome' => '', 'email' => '', 'cargo' => ''];
    $iniciais = '';
    foreach (array_slice(preg_split('/\s+/', trim((string) $autor['nome'])) ?: [], 0, 2) as $parte) {
        $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
    }
    $quando = $d['enviado_em'] ? date('d/m/Y \à\s H:i', strtotime((string) $d['enviado_em'])) : '';

    // Linhas do resumo: rótulo à esquerda, valor à direita, separadas por fio.
    $resumo = [
        ['Onde aparece', $d['onde']],
        ['Obrigatório para', $d['departamentos'] ? implode(', ', $d['departamentos']) : 'Todos os departamentos'],
        ['Origem do vídeo', $d['origem']],
    ];
    if (!empty($d['duracao_min'])) {
        $resumo[] = ['Duração informada', $d['duracao_min'] . ' min'];
    }
    $resumo[] = ['Perguntas', $d['perguntas'] ? count($d['perguntas']) . (count($d['perguntas']) === 1 ? ' pergunta' : ' perguntas') : 'Nenhuma'];
    $linhasResumo = '';
    foreach ($resumo as $i => [$rotulo, $valor]) {
        $borda = $i ? "border-top:1px solid {$linha};" : '';
        $linhasResumo .= "<tr>"
            . "<td style=\"{$fonte}{$borda}padding:11px 0;font-size:13px;color:{$cinza};width:42%;vertical-align:top\">{$e($rotulo)}</td>"
            . "<td style=\"{$fonte}{$borda}padding:11px 0;font-size:14px;color:{$tinta};font-weight:600;vertical-align:top\">{$e($valor)}</td>"
            . "</tr>";
    }

    $descricao = trim((string) $d['descricao']) !== ''
        ? "<p style=\"{$fonte}margin:0 0 22px;font-size:14px;line-height:22px;color:{$tinta}\">" . nl2br($e($d['descricao'])) . '</p>'
        : '';

    // Até 5 perguntas, cada uma com o momento em que aparece.
    $blocoPerguntas = '';
    if ($d['perguntas']) {
        $itens = '';
        foreach (array_slice($d['perguntas'], 0, 5) as $p) {
            $quandoP = $p['momento_seg'] !== null
                ? sprintf('aos %02d:%02d', intdiv((int) $p['momento_seg'], 60), (int) $p['momento_seg'] % 60)
                : 'no fim';
            $itens .= "<tr>"
                . "<td style=\"{$fonte}padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap\">"
                . "<span style=\"display:inline-block;background:#FBEAEA;color:{$vermelho};font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px\">{$e($quandoP)}</span></td>"
                . "<td style=\"{$fonte}padding:6px 0;font-size:14px;line-height:20px;color:{$tinta}\">{$e($p['texto'])}</td>"
                . "</tr>";
        }
        $mais = count($d['perguntas']) > 5
            ? "<p style=\"{$fonte}margin:6px 0 0;font-size:12px;color:{$cinza}\">+ " . (count($d['perguntas']) - 5) . " na tela de aprovação</p>"
            : '';
        $blocoPerguntas = "<p style=\"{$fonte}margin:26px 0 8px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{$cinza}\">Perguntas do vídeo</p>"
            . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">{$itens}</table>{$mais}";
    }

    // Foto do Portal (se veio) ou as iniciais num círculo escuro.
    $avatar = !empty($autor['foto'])
        ? "<img src=\"{$e($autor['foto'])}\" width=\"40\" height=\"40\" alt=\"{$e($iniciais ?: 'Foto')}\" style=\"display:block;width:40px;height:40px;border-radius:20px;object-fit:cover;background:#191C2B;color:#fff;font:700 14px/40px Arial,sans-serif;text-align:center;border:0\">"
        : "<div style=\"{$fonte}width:40px;height:40px;line-height:40px;border-radius:20px;background:#191C2B;color:#fff;text-align:center;font-size:14px;font-weight:700\">" . $e($iniciais ?: '?') . "</div>";

    $preheader = 'Enviado por ' . ($autor['nome'] ?: 'um colaborador') . ' — assista e aprove ou recuse.';
    $link = $e($d['link']);

    return '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1"><title>Vídeo para aprovação</title></head>'
        . "<body style=\"margin:0;padding:0;background:#F3F2EF\">"
        // Texto que aparece na prévia da caixa de entrada, escondido no corpo.
        . "<div style=\"display:none;max-height:0;overflow:hidden;opacity:0\">{$e($preheader)}</div>"
        . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#F3F2EF\"><tr><td align=\"center\" style=\"padding:28px 12px\">"
        . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px\">"

        // Cabeçalho da marca
        . "<tr><td style=\"background:#191C2B;border-radius:14px 14px 0 0;padding:18px 28px\">"
        . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\"><tr>"
        . "<td style=\"{$fonte}vertical-align:middle\">"
        . "<span style=\"display:inline-block;background:{$vermelho};color:#fff;font-size:12px;font-weight:800;padding:7px 9px;border-radius:7px;letter-spacing:-0.3px\">mse</span>"
        . "<span style=\"color:#fff;font-size:16px;font-weight:700;padding-left:10px;vertical-align:middle\">MSE Academy</span></td>"
        . "<td align=\"right\" style=\"{$fonte}font-size:12px;color:#A9AEBB;vertical-align:middle\">Aprovação de vídeo</td>"
        . "</tr></table></td></tr>"
        . "<tr><td style=\"background:{$vermelho};height:4px;line-height:4px;font-size:4px\">&nbsp;</td></tr>"

        // Corpo
        . "<tr><td style=\"background:#ffffff;padding:30px 28px 8px\">"
        . "<p style=\"{$fonte}margin:0 0 10px;font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:{$vermelho}\">Novo vídeo para aprovação</p>"
        . "<h1 style=\"{$fonte}margin:0 0 12px;font-size:24px;line-height:30px;color:{$tinta};font-weight:700\">{$e($d['titulo'])}</h1>"
        . "<span style=\"{$fonte}display:inline-block;background:#FFF4D6;color:#7A5A00;font-size:12px;font-weight:700;padding:5px 12px;border-radius:20px\">● Aguardando sua aprovação</span>"
        . "<p style=\"{$fonte}margin:18px 0 22px;font-size:14px;line-height:22px;color:{$cinza}\">Um colaborador enviou este vídeo para a MSE Academy. Ele <b style=\"color:{$tinta}\">só aparece para os colaboradores depois que um administrador aprovar</b>.</p>"
        . $descricao

        // Resumo
        . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#FAF8F5;border:1px solid {$linha};border-radius:10px\">"
        . "<tr><td style=\"padding:6px 18px\"><table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">{$linhasResumo}</table></td></tr></table>"

        . $blocoPerguntas

        // Quem enviou
        . "<p style=\"{$fonte}margin:26px 0 8px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{$cinza}\">Enviado por</p>"
        . "<table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\"><tr>"
        . "<td style=\"width:48px;vertical-align:middle\">" . $avatar . "</td>"
        . "<td style=\"{$fonte}padding-left:10px;vertical-align:middle\">"
        . "<div style=\"font-size:14px;font-weight:700;color:{$tinta}\">" . $e($autor['nome'] ?: 'Colaborador') . "</div>"
        . "<div style=\"font-size:12px;color:{$cinza};line-height:18px\">" . $e(implode(' · ', array_filter([$autor['cargo'], $autor['email']]))) . "</div>"
        . ($quando ? "<div style=\"font-size:12px;color:{$cinza};line-height:18px\">em {$e($quando)}</div>" : '')
        . "</td></tr></table>"

        // Botão
        . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:30px 0 8px\"><tr><td align=\"center\">"
        . "<table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"background:{$vermelho};border-radius:10px\">"
        . "<a href=\"{$link}\" style=\"{$fonte}display:inline-block;padding:15px 34px;font-size:16px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px\">Abrir tela de aprovação &rarr;</a>"
        . "</td></tr></table>"
        . "<p style=\"{$fonte}margin:12px 0 0;font-size:12px;color:{$cinza}\">Lá você assiste ao vídeo, confere as perguntas e aprova ou recusa.</p>"
        . "</td></tr></table>"
        . "</td></tr>"

        // Respiro antes do rodapé
        . "<tr><td style=\"background:#ffffff;height:18px;line-height:18px;font-size:18px\">&nbsp;</td></tr>"

        // Rodapé
        . "<tr><td style=\"background:#ffffff;border-top:1px solid {$linha};border-radius:0 0 14px 14px;padding:16px 28px;{$fonte}font-size:11px;line-height:17px;color:#9AA0AA;text-align:center\">"
        . "Mensagem automática da MSE Academy · MSE Engenharia<br>Você recebeu porque está na lista de aprovação de vídeos da Academy. Não é preciso responder."
        . "</td></tr>"

        . '</table></td></tr></table></body></html>';
}

/** A mesma mensagem em texto puro (para quem lê e-mail sem HTML). */
function mse_email_aprovacao_texto(array $d): string
{
    $autor = $d['autor'] + ['nome' => '', 'email' => '', 'cargo' => ''];
    $t = "NOVO VÍDEO PARA APROVAÇÃO — MSE Academy\n\n";
    $t .= $d['titulo'] . "\n" . str_repeat('-', min(60, mb_strlen($d['titulo']))) . "\n\n";
    if (trim((string) $d['descricao']) !== '') {
        $t .= trim($d['descricao']) . "\n\n";
    }
    $t .= 'Onde aparece: ' . $d['onde'] . "\n";
    $t .= 'Obrigatório para: ' . ($d['departamentos'] ? implode(', ', $d['departamentos']) : 'Todos os departamentos') . "\n";
    $t .= 'Origem do vídeo: ' . $d['origem'] . "\n";
    if (!empty($d['duracao_min'])) {
        $t .= 'Duração informada: ' . $d['duracao_min'] . " min\n";
    }
    $t .= 'Perguntas: ' . ($d['perguntas'] ? count($d['perguntas']) : 'nenhuma') . "\n";
    foreach (array_slice($d['perguntas'], 0, 5) as $p) {
        $q = $p['momento_seg'] !== null ? sprintf('aos %02d:%02d', intdiv((int) $p['momento_seg'], 60), (int) $p['momento_seg'] % 60) : 'no fim';
        $t .= "  • ({$q}) {$p['texto']}\n";
    }
    $t .= "\nEnviado por: " . implode(' · ', array_filter([$autor['nome'], $autor['cargo'], $autor['email']])) . "\n";
    $t .= "\nAbrir tela de aprovação: {$d['link']}\n";
    $t .= "\nO vídeo só aparece para os colaboradores depois que um administrador aprovar.\n";
    return $t;
}

/**
 * Os dados do e-mail de um vídeo enviado: título, onde aparece,
 * departamentos, perguntas (com o momento), quem enviou e o link.
 * Usado pelo aviso de verdade e pelo "Testar e-mail" (que manda o mesmo
 * e-mail, montado com o envio mais recente). null = vídeo não existe.
 */
function mse_email_aprovacao_dados(PDO $pdo, int $courseId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT c.id, c.title, c.description, c.type, c.video_source, c.duration_minutes, c.enviado_por, c.enviado_em,
                a.name AS area_name
         FROM courses c LEFT JOIN areas a ON a.id = c.area_id
         WHERE c.id = ?'
    );
    $stmt->execute([$courseId]);
    $curso = $stmt->fetch();
    if (!$curso) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT name, email, cargo, cpf FROM users WHERE id = ?');
    $stmt->execute([(int) $curso['enviado_por']]);
    $autor = $stmt->fetch() ?: ['name' => '', 'email' => '', 'cargo' => '', 'cpf' => ''];

    // Foto do cadastro no Portal (ficha de funcionário). Se a API não
    // responder ou não achar, o e-mail usa as iniciais — nunca trava o aviso.
    $foto = null;
    try {
        $ficha = mse_portal_ficha_buscar_pessoa($autor['cpf'] ?? null, $autor['name'] ?? null)['ficha'] ?? null;
        $foto = $ficha['foto'] ?? null;
    } catch (Throwable $e) {
        error_log('[email aprovacao] foto: ' . $e->getMessage());
    }

    $temMomento = mse_tem_coluna($pdo, 'quiz_questions', 'momento_seg');
    $stmt = $pdo->prepare(
        'SELECT question_text, ' . ($temMomento ? 'momento_seg' : 'NULL AS momento_seg')
        . ' FROM quiz_questions WHERE course_id = ? ORDER BY order_index ASC, id ASC'
    );
    $stmt->execute([$courseId]);
    $perguntas = array_map(static fn($q) => [
        'texto' => $q['question_text'],
        'momento_seg' => $q['momento_seg'] !== null ? (int) $q['momento_seg'] : null,
    ], $stmt->fetchAll());

    $departamentos = mse_departamentos_do_curso($pdo, $courseId);
    if (mse_tem_tabela($pdo, 'course_areas')) {
        $stmt = $pdo->prepare('SELECT a.name FROM course_areas ca JOIN areas a ON a.id = ca.area_id WHERE ca.course_id = ? ORDER BY a.name');
        $stmt->execute([$courseId]);
        $departamentos = array_merge($departamentos, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    return [
        'titulo' => $curso['title'],
        'descricao' => (string) $curso['description'],
        'onde' => $curso['type'] === 'onboarding' ? 'Integração (trilha obrigatória)' : 'Curso · ' . ($curso['area_name'] ?: 'sem área'),
        'departamentos' => $departamentos,
        'origem' => ['youtube' => 'YouTube', 's3' => 'Arquivo enviado', 'playlist' => 'Playlist do YouTube'][$curso['video_source']] ?? $curso['video_source'],
        'duracao_min' => (int) $curso['duration_minutes'],
        'perguntas' => $perguntas,
        'autor' => ['nome' => $autor['name'], 'email' => $autor['email'], 'cargo' => $autor['cargo'], 'foto' => $foto],
        'enviado_em' => $curso['enviado_em'],
        'link' => mse_url_academy() . '/?aprovar=' . $courseId,
    ];
}

/** Dados de exemplo para o "Testar e-mail" quando ainda não há nenhum vídeo enviado. */
function mse_email_aprovacao_exemplo(array $admin): array
{
    return [
        'titulo' => 'Trabalho em altura: uso correto do cinto paraquedista (exemplo)',
        'descricao' => 'Este é um e-mail de teste. O aviso de verdade traz aqui a descrição escrita por quem enviou o vídeo.',
        'onde' => 'Integração (trilha obrigatória)',
        'departamentos' => ['HSE', 'OBRAS'],
        'origem' => 'Arquivo enviado',
        'duracao_min' => 12,
        'perguntas' => [
            ['texto' => 'Antes de usar, o que deve ser inspecionado no cinto?', 'momento_seg' => 95],
            ['texto' => 'Qual NR trata de trabalho em altura?', 'momento_seg' => null],
        ],
        'autor' => ['nome' => (string) ($admin['name'] ?? 'Colaborador'), 'email' => (string) ($admin['email'] ?? ''), 'cargo' => (string) ($admin['cargo'] ?? '')],
        'enviado_em' => (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s'),
        'link' => mse_url_academy() . '/',
    ];
}
