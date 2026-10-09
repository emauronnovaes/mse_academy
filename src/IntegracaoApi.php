<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php';

/**
 * Cliente da API de integração do Hub MSE (api_integracao2) — de onde vem
 * quem foi admitido e quando. Só leitura.
 *
 * .env do servidor:
 *   INTEGRACAO_API_BASE   padrão: https://portalmse.com.br/microservices/hub_mse/api_integracao2
 *   INTEGRACAO_API_TOKEN  token Bearer (sem ele, tenta o PORTAL_FICHA_API_TOKEN)
 *
 * GET /v1/integracoes devolve as integrações do RH (status admissao,
 * finalizada...). Os nomes dos campos são lidos com alternativas, porque a
 * API não documenta o formato da resposta — se algum essencial (nome ou
 * data de admissão) não vier, o erro lista os campos recebidos, sem os
 * valores, pra ajustar aqui.
 */

const MSE_INTEGRACAO_API_PADRAO = 'https://portalmse.com.br/microservices/hub_mse/api_integracao2';

function mse_integracao_token(): string
{
    return trim(mse_env('INTEGRACAO_API_TOKEN')) ?: trim(mse_env('PORTAL_FICHA_API_TOKEN'));
}

/** GET na API. Devolve o JSON decodificado ou lança RuntimeException com o motivo. */
function mse_integracao_get(string $caminho, array $query): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão curl do PHP não está instalada no servidor.');
    }
    $token = mse_integracao_token();
    if ($token === '') {
        throw new RuntimeException('Falta INTEGRACAO_API_TOKEN no .env do servidor.');
    }
    $base = rtrim(trim(mse_env('INTEGRACAO_API_BASE')) ?: MSE_INTEGRACAO_API_PADRAO, '/');

    $ch = curl_init($base . $caminho . ($query ? '?' . http_build_query($query) : ''));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
    ]);
    $resposta = curl_exec($ch);
    $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erroRede = curl_error($ch);
    curl_close($ch);

    if ($resposta === false || $erroRede !== '') {
        throw new RuntimeException('Não consegui falar com a API de integração: ' . $erroRede);
    }
    $dados = json_decode((string) $resposta, true);
    if ($codigo < 200 || $codigo >= 300) {
        $motivo = is_array($dados) ? (string) ($dados['error'] ?? $dados['message'] ?? $dados['mensagem'] ?? '') : '';
        $dica = in_array($codigo, [401, 403], true) ? ' (token recusado — confira INTEGRACAO_API_TOKEN)' : '';
        throw new RuntimeException("A API de integração respondeu HTTP {$codigo}{$dica}" . ($motivo !== '' ? ": {$motivo}" : '.'));
    }
    if (!is_array($dados)) {
        throw new RuntimeException('A API de integração devolveu uma resposta que não é JSON.');
    }
    return $dados;
}

/** A lista de registros dentro da resposta (data, items, ... ou a própria resposta). */
function mse_integracao_registros(array $dados): array
{
    foreach (['data', 'dados', 'items', 'itens', 'integracoes', 'resultados', 'results', 'registros'] as $chave) {
        if (isset($dados[$chave]) && is_array($dados[$chave])) {
            return array_values(array_filter($dados[$chave], 'is_array'));
        }
    }
    return array_keys($dados) === range(0, count($dados) - 1) ? array_values(array_filter($dados, 'is_array')) : [];
}

/**
 * Primeiro valor preenchido entre os nomes de campo, procurando no registro
 * e um nível abaixo (a "última admissão ativa" vem num objeto à parte).
 * Objeto com "nome"/"descricao" (ex.: funcao: {id, nome}) vira o texto.
 */
function mse_integracao_campo(array $registro, array $nomes): ?string
{
    $niveis = [$registro];
    foreach ($registro as $valor) {
        if (is_array($valor) && array_keys($valor) !== range(0, count($valor) - 1)) {
            $niveis[] = $valor;
        }
    }
    foreach ($niveis as $nivel) {
        foreach ($nomes as $nome) {
            if (!array_key_exists($nome, $nivel)) {
                continue;
            }
            $v = $nivel[$nome];
            if (is_array($v)) {
                $v = $v['nome'] ?? $v['descricao'] ?? $v['name'] ?? null;
            }
            if (is_scalar($v) && trim((string) $v) !== '') {
                return trim((string) $v);
            }
        }
    }
    return null;
}

/** "2026-09-17", "2026-09-17 08:00:00" ou "17/09/2026" → "2026-09-17". */
function mse_integracao_data(?string $v): ?string
{
    if ($v === null) {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $v, $m)) {
        return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})#', $v, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    return null;
}

/**
 * Quem foi admitido entre $de e $ate (YYYY-MM-DD, inclusive).
 *
 * Busca as integrações em andamento (status=admissao) e as finalizadas,
 * da admissão mais recente pra mais antiga, e para de paginar quando
 * passa de $de. Fica de fora quem a API marca como admitido = não.
 */
function mse_integracao_admitidos(string $de, string $ate, ?string &$aviso = null): array
{
    // Limites pra nunca ficar pendurado: 40 s no total e 12 páginas (2.400
    // registros) por aba. Se estourar, devolve o que achou e diz no $aviso.
    $inicio = microtime(true);
    $porPagina = 200;
    $pessoas = [];
    $vistos = [];
    $campos = [];
    $algumaData = false;

    foreach (['admissao', 'finalizada'] as $status) {
        for ($pagina = 1; $pagina <= 12; $pagina++) {
            if (microtime(true) - $inicio > 40) {
                $aviso = 'A API de integração demorou demais; a lista pode estar incompleta. Tente um período menor.';
                break 2;
            }
            $registros = mse_integracao_registros(mse_integracao_get('/v1/integracoes', [
                'status' => $status,
                'order_by' => 'data_admissao',
                'order_dir' => 'DESC',
                'per_page' => $porPagina,
                'page' => $pagina,
            ]));
            $passouDoPeriodo = false;

            foreach ($registros as $r) {
                if (!$campos) {
                    $campos = array_keys($r);
                }
                $admissao = mse_integracao_data(mse_integracao_campo($r, ['data_admissao', 'dt_admissao', 'admissao_data', 'data_de_admissao']));
                if ($admissao === null) {
                    continue;
                }
                $algumaData = true;
                if ($admissao < $de) {
                    $passouDoPeriodo = true;
                    continue;
                }
                if ($admissao > $ate) {
                    continue;
                }
                $admitido = mse_integracao_campo($r, ['admitido']);
                if ($admitido !== null && in_array(mb_strtolower($admitido), ['nao', 'não', 'n', '0', 'false'], true)) {
                    continue;
                }

                $cpf = preg_replace('/\D/', '', (string) mse_integracao_campo($r, ['cpf', 'cpf_funcionario']));
                $nome = (string) mse_integracao_campo($r, ['nome', 'nome_completo', 'nome_funcionario']);
                $chave = $cpf !== '' ? 'cpf:' . $cpf : 'nome:' . mse_normalize_text($nome) . '|' . $admissao;
                if ($nome === '' || isset($vistos[$chave])) {
                    continue; // a mesma pessoa pode aparecer nas duas abas
                }
                $vistos[$chave] = true;

                $pessoas[] = [
                    'nome' => $nome,
                    'cpf' => $cpf !== '' ? $cpf : null,
                    'data_admissao' => $admissao,
                    'funcao' => mse_integracao_campo($r, ['funcao_nome', 'nome_funcao', 'funcao', 'cargo']),
                    'obra' => mse_integracao_campo($r, ['obra_nome', 'nome_obra', 'obra']),
                    'vinculo' => mse_integracao_campo($r, ['funcionario', 'mse_terceiro']),
                    'empresa' => mse_integracao_campo($r, ['empresa_contratante_nome', 'empresa_nome', 'empresa_contratante']),
                ];
            }

            if (count($registros) < $porPagina || $passouDoPeriodo) {
                break;
            }
            if ($pagina === 12) {
                $aviso = 'Muitos registros na API de integração; a lista pode estar incompleta. Tente um período menor.';
            }
        }
    }

    if ($campos && !$algumaData) {
        throw new RuntimeException('A API de integração não trouxe a data de admissão. Campos recebidos: ' . implode(', ', $campos) . '.');
    }

    usort($pessoas, static fn($a, $b) => [$b['data_admissao'], $a['nome']] <=> [$a['data_admissao'], $b['nome']]);
    return $pessoas;
}
