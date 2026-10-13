<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Aprovacao.php';
require_once __DIR__ . '/../../../src/Assuntos.php';
require_once __DIR__ . '/../../../src/Classificacao.php';

/**
 * Aprova ou recusa um vídeo enviado para aprovação.
 *
 * Aprovar publica na hora (is_published = 1), como se um admin tivesse
 * cadastrado. Recusar mantém escondido e guarda o motivo — nada é apagado.
 * Recusar apaga o arquivo do vídeo da AWS (⚠ irreversível): o registro
 * (título, perguntas, motivo) fica no histórico de recusados, mas aprovar
 * depois só é possível se o arquivo ainda existir (YouTube, ou o envio que
 * falhou em apagar). Um vídeo recusado ainda pode ser aprovado depois (o
 * histórico de recusados fica na tela de aprovação). Se dois admins decidirem ao mesmo
 * tempo, vale o primeiro.
 *
 * Tema sugerido ("Outro" no Tema): decidido junto, mas separado do vídeo.
 *   aprovar + tema 'aprovar'  -> publica o vídeo e cria o assunto (o vídeo entra nele);
 *   aprovar + tema 'recusar'  -> publica o vídeo, não cria o assunto; se o vídeo
 *                                não tem assunto, area_slug diz em qual ele fica;
 *   recusar                   -> recusa o vídeo e o tema.
 *
 * POST {course_id, acao: 'aprovar' | 'recusar', motivo?, tema?: 'aprovar' | 'recusar', area_slug?}
 */

mse_cors();
$admin = mse_require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$input = mse_input();
$courseId = (int) ($input['course_id'] ?? 0);
$acao = (string) ($input['acao'] ?? '');
$motivo = mb_substr(trim((string) ($input['motivo'] ?? '')), 0, 500);
if ($courseId <= 0 || !in_array($acao, ['aprovar', 'recusar'], true)) {
    mse_error('Informe course_id e acao ("aprovar" ou "recusar").', 422);
}

$pdo = mse_db();
if (!mse_tem_coluna($pdo, 'courses', 'aprovacao_status')) {
    mse_error('Vídeo não encontrado.', 404);
}

$temClassif = mse_tem_coluna($pdo, 'courses', 'tema_status');
$stmt = $pdo->prepare('SELECT video_source, video_key, aprovacao_status, area_id, '
    . ($temClassif ? 'tema_sugerido, tema_status' : 'NULL AS tema_sugerido, NULL AS tema_status') . ' FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$c = $stmt->fetch();

// Vídeo recusado cujo arquivo já foi apagado da AWS não pode ser aprovado.
if ($acao === 'aprovar' && $c && $c['aprovacao_status'] === 'recusado' && $c['video_source'] === 's3' && trim((string) $c['video_key']) === '') {
    mse_error('O arquivo deste vídeo foi apagado da AWS quando ele foi recusado. Peça para a pessoa enviar o vídeo de novo.', 409);
}

// Tema sugerido esperando decisão.
$temaPendente = $c && $c['tema_status'] === 'pendente' && trim((string) $c['tema_sugerido']) !== '';
$tema = (string) ($input['tema'] ?? '');
$assuntoEscolhido = null;
$assuntosAtuais = $c ? (mse_assuntos_mapa($pdo, [$courseId])[$courseId] ?? ($c['area_id'] ? [(int) $c['area_id']] : [])) : [];
if ($acao === 'aprovar' && $temaPendente) {
    if (!in_array($tema, ['aprovar', 'recusar'], true)) {
        mse_error('Decida o tema sugerido ("' . $c['tema_sugerido'] . '"): aprovar ou recusar.', 422);
    }
    if ($tema === 'recusar' && !$assuntosAtuais) {
        $slug = trim((string) ($input['area_slug'] ?? ''));
        $st = $pdo->prepare('SELECT id FROM areas WHERE slug = ?');
        $st->execute([$slug]);
        $assuntoEscolhido = $st->fetchColumn();
        if ($slug === '' || $assuntoEscolhido === false) {
            mse_error('O tema foi recusado e o vídeo ainda não tem assunto: escolha em qual assunto ele vai ficar.', 422);
        }
        $assuntoEscolhido = (int) $assuntoEscolhido;
    }
    mse_garantir_tabela_assuntos($pdo); // antes da transação (criar tabela encerraria a transação)
}

$pdo->beginTransaction();
$stmt = $pdo->prepare(
    $acao === 'aprovar'
        ? "UPDATE courses SET is_published = 1, aprovacao_status = 'aprovado', decidido_por = ?, decidido_em = NOW(), motivo_recusa = NULL
           WHERE id = ? AND aprovacao_status IN ('pendente', 'recusado')"
        : "UPDATE courses SET is_published = 0, aprovacao_status = 'recusado', decidido_por = ?, decidido_em = NOW(), motivo_recusa = ?
           WHERE id = ? AND aprovacao_status = 'pendente'"
);
$stmt->execute($acao === 'aprovar' ? [(int) $admin['id'], $courseId] : [(int) $admin['id'], $motivo ?: null, $courseId]);

if ($stmt->rowCount() === 0) {
    $pdo->rollBack();
    $stmt = $pdo->prepare(
        'SELECT c.aprovacao_status, u.name FROM courses c LEFT JOIN users u ON u.id = c.decidido_por WHERE c.id = ?'
    );
    $stmt->execute([$courseId]);
    $atual = $stmt->fetch();
    if (!$atual || $atual['aprovacao_status'] === null) {
        mse_error('Vídeo não encontrado.', 404);
    }
    $foi = $atual['aprovacao_status'] === 'aprovado' ? 'aprovado' : 'recusado';
    mse_error("Esse vídeo já foi {$foi}" . ($atual['name'] ? " por {$atual['name']}" : '') . '.', 409);
}

try {
    if ($acao === 'aprovar' && $temaPendente) {
        $novos = $assuntosAtuais;
        if ($tema === 'aprovar') {
            $novoId = mse_criar_ou_achar_assunto($pdo, (string) $c['tema_sugerido']);
            // Sem assunto, o tema novo vira o principal; com assunto, entra como mais um.
            $novos = $c['area_id'] ? array_merge($novos, [$novoId]) : array_merge([$novoId], $novos);
        } elseif ($assuntoEscolhido !== null) {
            $novos = [$assuntoEscolhido];
        }
        $novos = array_values(array_unique(array_map('intval', $novos)));
        $pdo->prepare('UPDATE courses SET tema_status = ?, area_id = COALESCE(area_id, ?) WHERE id = ?')
            ->execute([$tema === 'aprovar' ? 'aprovado' : 'recusado', $novos[0] ?? null, $courseId]);
        if ($novos) {
            mse_gravar_assuntos($pdo, $courseId, $novos);
        }
    } elseif ($acao === 'recusar' && $temaPendente) {
        $pdo->prepare("UPDATE courses SET tema_status = 'recusado' WHERE id = ?")->execute([$courseId]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    mse_error('Não foi possível concluir (nada foi alterado): ' . $e->getMessage(), 500);
}

// Recusou: o arquivo do vídeo é apagado da AWS (⚠ irreversível — ver mse_excluir_arquivo_recusado).
$arquivo = $acao === 'recusar' ? mse_excluir_arquivo_recusado($pdo, $courseId) : ['excluido' => false, 'aviso' => null];

// Aulas mudaram: o banco de consulta se atualiza em até 1 minuto.
require_once __DIR__ . '/../../../src/Viniconsultas.php';
mse_vini_recalcular_agendado();

mse_json([
    'ok' => true,
    'status' => $acao === 'aprovar' ? 'aprovado' : 'recusado',
    'arquivo_excluido' => $arquivo['excluido'],
    'aviso_arquivo' => $arquivo['aviso'],
    'tema' => $temaPendente ? ($acao === 'aprovar' && $tema === 'aprovar' ? 'aprovado' : 'recusado') : null,
]);
