<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';

/**
 * Busca de teste na API de ficha de funcionários (só admin).
 *
 * Serve para conferir como o Portal devolve uma pessoa — por exemplo, quem
 * é "alocado na sede" mas trabalha na obra. Consulta as duas listas:
 *   /v1/ff_infos        fichas ATIVAS (a que a Academy usa)
 *   /v1/ff_infos_geral  todas, com status (ativo, desligado, aguardando...)
 *
 * Devolve só os campos de lotação (sede, obra, departamento, status...) e a
 * lista de nomes de campos — nada de CPF, telefone, endereço etc.
 *
 * GET ?busca=nome
 */

mse_cors();
mse_require_admin();

$busca = trim((string) ($_GET['busca'] ?? ''));
if (mb_strlen($busca) < 3) {
    mse_error('Digite pelo menos 3 letras do nome.', 422);
}
if (!function_exists('curl_init')) {
    mse_error('A extensão curl do PHP não está instalada no servidor.', 500);
}
$token = mse_env('PORTAL_FICHA_API_TOKEN', '');
if ($token === '') {
    mse_error('PORTAL_FICHA_API_TOKEN não está configurado no .env do servidor.', 409);
}
$base = rtrim(mse_env('PORTAL_FICHA_API_BASE', 'https://portalmse.com.br/microservices/hub_mse/api_ficha'), '/');

// Campos de lotação que interessam aqui (os demais só aparecem pelo nome).
// Campos que NÃO são mostrados (dados pessoais): aparecem só como "(oculto)".
const MSE_PADRAO_SENSIVEL = '/cpf|rg$|^rg|tel|fone|celular|e_?mail|nasc|sexo|doc|endere|cep|banco|agencia|conta|pix|senha|foto|salario|pis|ctps|cnh|filiacao|mae|pai/i';

const MSE_CAMPOS_LOTACAO = [
    'nome', 'status', 'situacao', 'mse_sede', 'nome_obra', 'obras_departamento', 'obra', 'departamento',
    'local_alojado', 'mobilizacao', 'desmobilizacao', 'funcao', 'tipo_contratacao', 'empresa_contratante',
    'centro_custo', 'municipio', 'uf',
];

$consultar = static function (string $caminho) use ($base, $token, $busca): array {
    $ch = curl_init($base . $caminho . '?' . http_build_query(['busca' => $busca]));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
    ]);
    $resposta = curl_exec($ch);
    $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro = curl_error($ch);
    curl_close($ch);

    if ($resposta === false || $erro !== '') {
        return ['erro' => 'Falha de rede: ' . $erro, 'pessoas' => [], 'campos' => []];
    }
    if ($codigo !== 200) {
        return ['erro' => "A API respondeu HTTP {$codigo}" . (in_array($codigo, [401, 403], true) ? ' (token recusado)' : ''), 'pessoas' => [], 'campos' => []];
    }
    $dados = json_decode((string) $resposta, true);
    $lista = is_array($dados) ? ($dados['data'] ?? $dados) : [];
    $lista = array_values(array_filter(is_array($lista) ? $lista : [], 'is_array'));

    $campos = [];
    $pessoas = [];
    $completo = null; // todos os campos da primeira pessoa (dados pessoais ocultos)
    foreach (array_slice($lista, 0, 15) as $i => $f) {
        if ($i === 0) {
            $completo = [];
            foreach ($f as $k => $v) {
                if (preg_match(MSE_PADRAO_SENSIVEL, (string) $k)) {
                    $completo[$k] = '(oculto)';
                } else {
                    $completo[$k] = is_scalar($v) || $v === null ? $v : '(lista/objeto)';
                }
            }
        }
        $campos = array_values(array_unique(array_merge($campos, array_keys($f))));
        $linha = [];
        foreach (MSE_CAMPOS_LOTACAO as $c) {
            if (array_key_exists($c, $f) && (is_scalar($f[$c]) || $f[$c] === null)) {
                $linha[$c] = $f[$c];
            }
        }
        $pessoas[] = $linha;
    }
    return ['erro' => null, 'total' => count($lista), 'pessoas' => $pessoas, 'campos' => $campos, 'completo' => $completo];
};

mse_json([
    'busca' => $busca,
    'ativos' => $consultar('/v1/ff_infos'),
    'geral' => $consultar('/v1/ff_infos_geral'),
]);
