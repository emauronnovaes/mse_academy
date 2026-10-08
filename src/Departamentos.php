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
    $ordenar = static function () use (&$unicos): array {
        $lista = array_values($unicos);
        usort($lista, static fn($a, $b) => strcmp(mse_departamento_chave($a), mse_departamento_chave($b)));
        return $lista;
    };

    $aviso = null;
    $doPortal = mse_departamentos_do_portal_cache($aviso);
    if ($doPortal) {
        $juntar($doPortal);
        return ['departamentos' => $ordenar(), 'fonte' => 'portal', 'aviso' => null];
    }

    if (mse_tem_coluna($pdo, 'users', 'setor_portal')) {
        $juntar($pdo->query("SELECT DISTINCT setor_portal FROM users WHERE setor_portal IS NOT NULL AND setor_portal <> ''")->fetchAll(PDO::FETCH_COLUMN));
    }
    if ($unicos) {
        return ['departamentos' => $ordenar(), 'fonte' => 'pessoas', 'aviso' => $aviso];
    }
    $juntar($pdo->query('SELECT name FROM areas ORDER BY name')->fetchAll(PDO::FETCH_COLUMN));
    return ['departamentos' => $ordenar(), 'fonte' => 'academy', 'aviso' => $aviso];
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
        if (is_array($f) && isset($f['obras_departamento']) && is_scalar($f['obras_departamento'])) {
            $nomes[] = trim((string) $f['obras_departamento']);
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
