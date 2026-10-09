<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';
require_once __DIR__ . '/PortalFichaApi.php';

/**
 * Departamentos do Portal — a lista que aparece em "Obrigatório para quais
 * departamentos?" ao adicionar um vídeo.
 *
 * A fonte é a API de ficha de funcionários (campo obras_departamento de
 * cada ficha ATIVA — só colaboradores ativos entram na lista): todos os departamentos que existem de fato no Portal,
 * não só os que têm cartão na Academy. A lista é guardada por 6 horas em
 * arquivo temporário do servidor, porque buscar todas as fichas leva alguns
 * segundos. Se a API falhar, usa os departamentos já gravados nas pessoas
 * (users.setor_portal) e, por último, os da Academy.
 *
 * Vale por nome (sem diferenciar maiúscula, acento ou pontuação): o
 * departamento da pessoa vem do Portal com o mesmo texto da lista.
 */

/**
 * O campo obras_departamento da ficha mistura departamentos (Programação,
 * Administrativo...) com NOMES DE OBRAS e contratos. Estes nomes de obra
 * não são departamento e ficam fora da lista de "obrigatório para".
 *
 * Para tirar mais um, é só acrescentar o nome aqui (maiúscula, acento,
 * espaço e pontuação não importam). Além desta lista, qualquer nome no
 * formato "CLIENTE - LOCAL" (com " - " no meio) é tratado como obra.
 */
const MSE_DEPARTAMENTOS_IGNORADOS = [
    'SCALA - CAMPINAS-SP - CP068',
    'PORTO ITAPOÁ',
    'MICROSOFT - SUMARÉ',
    'NESTLE - VARGEÃO',
    'NN - AP - ELETROMECÂNICA',
    'NN - REFORÇO EST. METÁLICAS',
    'NN - UB/SP - ELETROMECÂNICA',
    'LMS - COCAMAR MARINGÁ - GER.',
    'LMS - FITESA COSMÓPOLIS - GER.',
    'LMS - FORTGREEN MARINGÁ - GER.',
    'LMS - FORTGREEN VARGINHA - GER.',
    'CNPEM - AUDITÓRIO',
    'CNPEM-FASEADA',
    'IPEN - FAB. E MONT. CIRCUITO EXP.',
    'LOTE - 05 - CONSTRUÇÃO',
    'AMG - CP497',
    'ARENA MRV - CP343',
    'AWS',
    'EUROFARMA',
];

/** Esse texto é nome de obra (e não departamento)? A escolha do admin (tabela departamentos_config) vale mais que os padrões. */
function mse_departamento_eh_obra(string $nome, ?array $config = null): bool
{
    static $ignorados = null;
    if ($config !== null) {
        $tipo = $config[mse_departamento_chave($nome)]['tipo'] ?? null;
        if ($tipo === 'mostrar') {
            return false;
        }
        if ($tipo === 'ocultar') {
            return true;
        }
    }
    if ($ignorados === null) {
        $ignorados = array_flip(array_map(static fn($n) => str_replace(' ', '', mse_departamento_chave($n)), MSE_DEPARTAMENTOS_IGNORADOS));
    }
    if (isset($ignorados[str_replace(' ', '', mse_departamento_chave($nome))])) {
        return true;
    }
    return (bool) preg_match('/\s[-–]\s/u', $nome);
}

/**
 * Ajustes do admin na lista de departamentos (tela "Gerenciar lista"):
 *   ocultar  tira da lista um nome que o Portal manda
 *   mostrar  traz de volta um nome que estava oculto (padrão ou por regra)
 *   extra    departamento adicionado à mão, que não vem do Portal
 */
function mse_garantir_tabela_departamentos_config(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS departamentos_config (
               chave VARCHAR(150) NOT NULL, nome VARCHAR(150) NOT NULL, tipo VARCHAR(10) NOT NULL,
               criado_por VARCHAR(200) NULL, criado_em DATETIME NOT NULL, PRIMARY KEY (chave)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[departamentos_config] Não consegui criar a tabela (rode a migração 029): ' . $e->getMessage());
        return $ok = false;
    }
}

/** Ajustes gravados, por chave. Sem a tabela, nenhum. */
function mse_departamentos_config(PDO $pdo): array
{
    $st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $st->execute(['departamentos_config']);
    if ($st->fetchColumn() === false) {
        return [];
    }
    $mapa = [];
    foreach ($pdo->query('SELECT chave, nome, tipo FROM departamentos_config') as $l) {
        $mapa[$l['chave']] = $l;
    }
    return $mapa;
}

/** "Programação" e "PROGRAMACAO" viram a mesma coisa. */
function mse_departamento_chave(string $nome): string
{
    return mse_portal_ficha_normalizar_nome($nome);
}

/** Garante a tabela course_departamentos (migração 025). false = não existe e não deu pra criar. */
function mse_garantir_tabela_departamentos(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (mse_tem_tabela($pdo, 'course_departamentos')) {
        return $ok = true;
    }
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS course_departamentos (
               course_id INT UNSIGNED NOT NULL,
               departamento VARCHAR(150) NOT NULL,
               departamento_norm VARCHAR(150) NOT NULL,
               PRIMARY KEY (course_id, departamento_norm),
               CONSTRAINT fk_course_deptos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[departamentos] Não consegui criar a tabela (rode a migração 025): ' . $e->getMessage());
        return $ok = false;
    }
}

/**
 * Lista de departamentos do Portal, em ordem alfabética.
 *
 * @return array{departamentos: string[], fonte: string, aviso: ?string}
 *   fonte: "portal" (API de ficha), "pessoas" (gravados nas pessoas) ou "academy"
 */
function mse_departamentos_portal(PDO $pdo): array
{
    $b = mse_departamentos_base($pdo);
    $config = mse_departamentos_config($pdo);
    $lista = [];
    foreach ($b['nomes'] as $n) {
        if (!mse_departamento_eh_obra($n, $config)) {
            $lista[mse_departamento_chave($n)] = $n;
        }
    }
    foreach ($config as $chave => $c) { // departamentos adicionados à mão
        if ($c['tipo'] === 'extra' && !isset($lista[$chave])) {
            $lista[$chave] = $c['nome'];
        }
    }
    $nomes = array_values($lista);
    usort($nomes, static fn($a, $c) => strcmp(mse_departamento_chave($a), mse_departamento_chave($c)));
    return ['departamentos' => $nomes, 'fonte' => $b['fonte'], 'aviso' => $b['aviso']];
}

/**
 * Tudo para a tela "Gerenciar lista": cada nome que o Portal manda, se está
 * oculto, mais os departamentos adicionados à mão.
 *
 * @return array{itens: array<int, array{nome: string, oculto: bool, manual: bool}>, fonte: string, aviso: ?string}
 */
function mse_departamentos_gerenciar(PDO $pdo): array
{
    $b = mse_departamentos_base($pdo);
    $config = mse_departamentos_config($pdo);
    $itens = [];
    foreach ($b['nomes'] as $n) {
        $itens[mse_departamento_chave($n)] = ['nome' => $n, 'oculto' => mse_departamento_eh_obra($n, $config), 'manual' => false];
    }
    foreach ($config as $chave => $c) {
        if ($c['tipo'] === 'extra') {
            $itens[$chave] = ['nome' => $c['nome'], 'oculto' => false, 'manual' => true];
        }
    }
    $lista = array_values($itens);
    usort($lista, static fn($a, $c) => strcmp(mse_departamento_chave($a['nome']), mse_departamento_chave($c['nome'])));
    return ['itens' => $lista, 'fonte' => $b['fonte'], 'aviso' => $b['aviso']];
}

/** Nomes distintos que o Portal (ou, na falta, as pessoas / a Academy) informa, sem nenhum filtro. */
function mse_departamentos_base(PDO $pdo): array
{
    $unicos = [];
    $juntar = static function (array $nomes) use (&$unicos): void {
        foreach ($nomes as $n) {
            $n = trim((string) preg_replace('/\s+/', ' ', (string) $n));
            $chave = $n === '' ? '' : mse_departamento_chave($n);
            if ($chave !== '' && !isset($unicos[$chave])) {
                $unicos[$chave] = $n;
            }
        }
    };

    $aviso = null;
    $doPortal = mse_departamentos_do_portal_cache($aviso);
    if ($doPortal) {
        $juntar($doPortal);
        return ['nomes' => array_values($unicos), 'fonte' => 'portal', 'aviso' => null];
    }

    if (mse_tem_coluna($pdo, 'users', 'setor_portal')) {
        $juntar($pdo->query("SELECT DISTINCT setor_portal FROM users WHERE setor_portal IS NOT NULL AND setor_portal <> ''")->fetchAll(PDO::FETCH_COLUMN));
    }
    if ($unicos) {
        return ['nomes' => array_values($unicos), 'fonte' => 'pessoas', 'aviso' => $aviso];
    }
    $juntar($pdo->query('SELECT name FROM areas ORDER BY name')->fetchAll(PDO::FETCH_COLUMN));
    return ['nomes' => array_values($unicos), 'fonte' => 'academy', 'aviso' => $aviso];
}

/** Departamentos vindos da API de ficha, com cache de 6h. [] se falhar (e $aviso diz por quê). */
function mse_departamentos_do_portal_cache(?string &$aviso): array
{
    $arquivo = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'mse_academy_departamentos_portal.json';
    if (is_file($arquivo) && time() - (int) filemtime($arquivo) < 6 * 3600) {
        $dados = json_decode((string) @file_get_contents($arquivo), true);
        if (is_array($dados) && $dados) {
            return $dados;
        }
    }

    try {
        $lista = mse_departamentos_da_api_ficha();
    } catch (Throwable $e) {
        $aviso = $e->getMessage();
        // Cache vencido ainda serve melhor que nada.
        $dados = is_file($arquivo) ? json_decode((string) @file_get_contents($arquivo), true) : null;
        return is_array($dados) ? $dados : [];
    }
    if ($lista) {
        @file_put_contents($arquivo, json_encode($lista, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    return $lista;
}

/** GET /v1/ff_infos?all=1 e junta os obras_departamento distintos. */
function mse_departamentos_da_api_ficha(): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão curl do PHP não está instalada no servidor.');
    }
    $token = mse_env('PORTAL_FICHA_API_TOKEN', '');
    if ($token === '') {
        throw new RuntimeException('PORTAL_FICHA_API_TOKEN não está configurado no .env do servidor.');
    }
    $base = rtrim(mse_env('PORTAL_FICHA_API_BASE', 'https://portalmse.com.br/microservices/hub_mse/api_ficha'), '/');

    $ch = curl_init($base . '/v1/ff_infos?all=1');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
    ]);
    $resposta = curl_exec($ch);
    $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro = curl_error($ch);
    curl_close($ch);

    if ($resposta === false || $erro !== '') {
        throw new RuntimeException('Falha de rede ao buscar os departamentos no Portal: ' . $erro);
    }
    if ($codigo !== 200) {
        throw new RuntimeException("A API do Portal respondeu HTTP {$codigo}"
            . (in_array($codigo, [401, 403], true) ? ' (token recusado — confira PORTAL_FICHA_API_TOKEN).' : '.'));
    }
    $dados = json_decode((string) $resposta, true);
    $fichas = is_array($dados) ? ($dados['data'] ?? $dados) : [];

    $nomes = [];
    foreach ($fichas as $f) {
        // /v1/ff_infos já devolve só fichas ativas; se algum registro trouxer
        // status de desligado, desistente ou aguardando, fica de fora mesmo assim.
        $situacao = is_array($f) ? mse_departamento_chave((string) ($f['status'] ?? $f['situacao'] ?? '')) : '';
        if ($situacao !== '' && !in_array($situacao, ['ativo', 'ativa', '1', 'sim', 'active'], true)) {
            continue;
        }
        if (is_array($f)) {
            $d = mse_ficha_departamento($f); // já troca nome de obra pelo departamento, se a ficha trouxer
            if ($d !== null && $d !== '') {
                $nomes[] = $d;
            }
        }
    }
    return array_values(array_filter(array_unique($nomes), 'strlen'));
}

/** Departamentos marcados num vídeo (nomes como foram escolhidos). */
function mse_departamentos_do_curso(PDO $pdo, int $courseId): array
{
    if (!mse_tem_tabela($pdo, 'course_departamentos')) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT departamento FROM course_departamentos WHERE course_id = ? ORDER BY departamento');
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
