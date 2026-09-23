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
 * Acha a área da Academy que corresponde ao setor vindo do RH.
 *
 * O RH escreve o setor livremente ("Tecnologia da Informação", "T.I.",
 * "financeiro"), então comparar texto exato não funcionaria. Aqui a
 * comparação ignora acento, maiúscula, pontuação e espaço sobrando, e
 * tenta tanto o nome quanto o slug da área.
 *
 * Devolve null quando não há correspondência — nesse caso a área da
 * pessoa fica como estava, em vez de ser apagada por um palpite errado.
 */
function mse_area_id_por_setor(PDO $pdo, ?string $setor): ?int
{
    $setor = trim((string) $setor);
    if ($setor === '') {
        return null;
    }

    $normalizar = static function (string $t): string {
        $t = mb_strtolower($t, 'UTF-8');
        // Tira acentos sem depender da extensão intl, que nem todo
        // servidor tem instalada.
        $t = strtr($t, [
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'é'=>'e','ê'=>'e','è'=>'e','ë'=>'e',
            'í'=>'i','î'=>'i','ì'=>'i','ï'=>'i',
            'ó'=>'o','õ'=>'o','ô'=>'o','ò'=>'o','ö'=>'o',
            'ú'=>'u','û'=>'u','ù'=>'u','ü'=>'u',
            'ç'=>'c',
        ]);
        // Qualquer coisa que não seja letra ou número vira espaço, e
        // espaços repetidos viram um só: "T.I." e "TI" passam a bater.
        $t = preg_replace('/[^a-z0-9]+/', ' ', $t);
        return trim($t);
    };

    $alvo = $normalizar($setor);
    if ($alvo === '') {
        return null;
    }
    // Sem espaço nenhum: é o que faz "T.I." casar com "TI", já que a
    // pontuação vira espaço na normalização.
    $alvoCompacto = str_replace(' ', '', $alvo);

    foreach ($pdo->query('SELECT id, slug, name FROM areas') as $area) {
        $nome = $normalizar($area['name']);
        $slug = $normalizar($area['slug']);
        if ($nome === $alvo || $slug === $alvo) {
            return (int) $area['id'];
        }
        if (str_replace(' ', '', $nome) === $alvoCompacto || str_replace(' ', '', $slug) === $alvoCompacto) {
            return (int) $area['id'];
        }
    }

    error_log("[mse_area_id_por_setor] Setor \"{$setor}\" não corresponde a nenhuma área cadastrada.");
    return null;
}

/**
 * Deduz a área a partir do CARGO, usando o mapeamento que o admin
 * cadastra (tabela cargo_areas, migração 017).
 *
 * Usado só quando nem o RH nem o token do Portal informaram o setor.
 * Ex: cargo "Analista de Segurança do Trabalho Jr" contém a palavra
 * "seguranca do trabalho", então a pessoa é dessa área.
 *
 * Casa pela palavra MAIS LONGA primeiro: se existirem "seguranca" e
 * "seguranca do trabalho" cadastradas, a mais específica ganha — senão
 * a ordem no banco decidiria, o que daria resultado imprevisível.
 */
function mse_area_id_por_cargo(PDO $pdo, ?string $cargo): ?int
{
    $cargo = trim((string) $cargo);
    if ($cargo === '') {
        return null;
    }
    if ($pdo->query("SHOW TABLES LIKE 'cargo_areas'")->fetch() === false) {
        return null; // migração 017 ainda não rodou neste servidor
    }

    $alvo = mse_normalize_text($cargo);
    if ($alvo === '') {
        return null;
    }

    $stmt = $pdo->query('SELECT palavra, area_id FROM cargo_areas ORDER BY CHAR_LENGTH(palavra) DESC');
    foreach ($stmt->fetchAll() as $linha) {
        $palavra = mse_normalize_text((string) $linha['palavra']);
        if ($palavra !== '' && mse_str_contains($alvo, $palavra)) {
            return (int) $linha['area_id'];
        }
    }
    return null;
}

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
