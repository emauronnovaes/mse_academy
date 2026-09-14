<?php
declare(strict_types=1);

/**
 * Cliente da API "ff_infos" do Hub MSE — busca dados oficiais da ficha
 * funcional (RH) de um colaborador, dado o nome ou CPF dele.
 *
 * IMPORTANTE: essa API NÃO diz "quem está logado agora" — ela só busca
 * dados de alguém que você já identificou por outro meio. Quem realmente
 * identifica a pessoa é o token assinado do Portal (?sso=..., já
 * implementado em src/Sso.php). Isso aqui é usado DEPOIS, só pra
 * completar informações que o token do Portal não trouxe (ex: a função/
 * cargo oficial do RH, pra melhorar a recomendação de cursos por cargo).
 *
 * A chave da API (PORTAL_FICHA_API_TOKEN) é OBRIGATORIAMENTE lida do
 * servidor (.env) — nunca é enviada nem exposta pro navegador da pessoa.
 */

/**
 * Busca a ficha funcional pelo nome (ou CPF). Devolve o PRIMEIRO
 * resultado encontrado, ou null se não achar nada ou a API falhar —
 * uma falha aqui NUNCA deve impedir o login (é só um enriquecimento
 * opcional, o login já funciona só com o token do Portal).
 *
 * @return array{nome:string, cpf:?string, funcao:?string, obras_departamento:?string, email:?string}|null
 */
function mse_portal_ficha_buscar(string $termoBusca): ?array
{
    // Se a extensão curl não estiver instalada no PHP do servidor,
    // chamar curl_init() direto quebraria com erro fatal ("Call to
    // undefined function"), derrubando TODO o login (já que essa busca
    // roda em todo método de login, como enriquecimento). Isso é só um
    // extra — sem curl, simplesmente não enriquece, mas o login segue.
    if (!function_exists('curl_init')) {
        error_log('[mse_portal_ficha_buscar] Extensão curl do PHP não está instalada — pulando enriquecimento.');
        return null;
    }

    $baseUrl = rtrim(mse_env('PORTAL_FICHA_API_BASE', 'https://portalmse.com.br/microservices/hub_mse/api_ficha'), '/');
    $token = mse_env('PORTAL_FICHA_API_TOKEN', '');

    if ($token === '' || trim($termoBusca) === '') {
        return null;
    }

    $url = $baseUrl . '/v1/ff_infos?' . http_build_query(['busca' => $termoBusca]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5, // curta de propósito — nunca deve travar o login da pessoa
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        error_log('[mse_portal_ficha_buscar] Falha de rede: ' . $curlError);
        return null;
    }
    if ($httpCode !== 200) {
        error_log("[mse_portal_ficha_buscar] API respondeu HTTP {$httpCode} pra busca \"{$termoBusca}\"");
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['data'][0]) || !is_array($data['data'][0])) {
        return null; // nenhum resultado pra essa busca
    }

    $ficha = $data['data'][0];

    return [
        'nome' => (string) ($ficha['nome'] ?? ''),
        'cpf' => isset($ficha['cpf']) ? preg_replace('/\D/', '', (string) $ficha['cpf']) : null,
        // "funcao" na ficha é o cargo oficial do RH — mais confiável que
        // o que o token do Portal manda (se mandar).
        'funcao' => isset($ficha['funcao']) ? trim((string) $ficha['funcao']) : null,
        'obras_departamento' => isset($ficha['obras_departamento']) ? trim((string) $ficha['obras_departamento']) : null,
        // Documentação não lista "email" entre os campos, mas alguns
        // registros trazem — pegamos se vier, sem depender disso.
        'email' => isset($ficha['email']) && $ficha['email'] !== '' ? strtolower(trim((string) $ficha['email'])) : null,
    ];
}
