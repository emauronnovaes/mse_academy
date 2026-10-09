<?php
declare(strict_types=1);

// mse_area_id_por_cargo() usa mse_normalize_text(), que mora em
// Progress.php. O login (sso.php, portal_session.php) não carregava esse
// arquivo: com a API de ficha devolvendo cargo e o departamento sem
// correspondência na Academy, o login morria com erro fatal.
require_once __DIR__ . '/Progress.php';

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
    $r = mse_portal_ficha_consultar($termoBusca);
    if ($r['erro'] !== null) {
        error_log('[mse_portal_ficha_buscar] ' . $r['erro']);
    }
    return $r['ficha'];
}

/**
 * Mesma busca, mas dizendo POR QUE não veio nada. O login não precisa
 * saber (segue sem enriquecer); já a atualização de cargos pela tela de
 * treinamentos precisa mostrar o motivo — antes uma falha aqui deixava o
 * cargo vazio sem ninguém perceber.
 *
 * @return array{ficha: ?array, erro: ?string}  erro null + ficha null = não achou ninguém
 */
function mse_portal_ficha_consultar(string $termoBusca): array
{
    // Se a extensão curl não estiver instalada no PHP do servidor,
    // chamar curl_init() direto quebraria com erro fatal ("Call to
    // undefined function"), derrubando TODO o login (já que essa busca
    // roda em todo método de login, como enriquecimento). Isso é só um
    // extra — sem curl, simplesmente não enriquece, mas o login segue.
    if (!function_exists('curl_init')) {
        return ['ficha' => null, 'erro' => 'A extensão curl do PHP não está instalada no servidor.'];
    }

    $baseUrl = rtrim(mse_env('PORTAL_FICHA_API_BASE', 'https://portalmse.com.br/microservices/hub_mse/api_ficha'), '/');
    $token = mse_env('PORTAL_FICHA_API_TOKEN', '');

    if ($token === '') {
        return ['ficha' => null, 'erro' => 'PORTAL_FICHA_API_TOKEN não está configurado no .env do servidor.'];
    }
    if (trim($termoBusca) === '') {
        return ['ficha' => null, 'erro' => null];
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

    return mse_portal_ficha_interpretar($response, (int) $httpCode, $curlError);
}

/**
 * Lê a resposta da API de ficha (uma consulta). Além dos campos usados no
 * login, devolve em "bruta" a ficha inteira, como veio — é o que a tela de
 * novos contratados mostra em "Ver dados".
 *
 * @param string|false $response
 */
function mse_portal_ficha_interpretar($response, int $httpCode, string $curlError): array
{
    if ($response === false || $curlError !== '') {
        return ['ficha' => null, 'erro' => 'Falha de rede ao consultar a API do Portal: ' . $curlError];
    }
    if ($httpCode !== 200) {
        return ['ficha' => null, 'erro' => "A API do Portal respondeu HTTP {$httpCode}"
            . ($httpCode === 401 || $httpCode === 403 ? ' (token recusado — confira PORTAL_FICHA_API_TOKEN).' : '.')];
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['data'][0]) || !is_array($data['data'][0])) {
        return ['ficha' => null, 'erro' => null]; // nenhum resultado pra essa busca
    }

    $ficha = $data['data'][0];

    // O cargo pode vir com nomes diferentes conforme a versão da API: antes
    // só "funcao" era lido, e se viesse como "cargo" o nome chegava e o
    // cargo não. Usa o primeiro que existir.
    $cargo = null;
    foreach (['funcao', 'cargo', 'função', 'nome_funcao', 'funcao_nome', 'desc_funcao', 'descricao_funcao',
              'nome_cargo', 'cargo_nome', 'desc_cargo', 'descricao_cargo'] as $chave) {
        if (isset($ficha[$chave]) && is_scalar($ficha[$chave]) && trim((string) $ficha[$chave]) !== '') {
            $cargo = trim((string) $ficha[$chave]);
            break;
        }
    }

    return ['erro' => null, 'campos' => array_keys($ficha), 'bruta' => $ficha, 'ficha' => [
        'nome' => (string) ($ficha['nome'] ?? ''),
        'cpf' => isset($ficha['cpf']) ? preg_replace('/\D/', '', (string) $ficha['cpf']) : null,
        // "funcao" na ficha é o cargo oficial do RH — mais confiável que
        // o que o token do Portal manda (se mandar).
        'funcao' => $cargo,
        // O campo obras_departamento mistura departamento com NOME DE OBRA
        // (quem é alocado na sede mas trabalha numa obra vem com o nome da
        // obra). Aqui devolve o departamento de verdade, procurando em outros
        // campos da ficha quando o principal só traz uma obra. Todo o resto
        // da Academy já lê este campo, então passa a usar o certo sozinho.
        'obras_departamento' => mse_ficha_departamento($ficha),
        // Documentação não lista "email" entre os campos, mas alguns
        // registros trazem — pegamos se vier, sem depender disso.
        'email' => isset($ficha['email']) && $ficha['email'] !== '' ? strtolower(trim((string) $ficha['email'])) : null,
        // Foto do cadastro no Portal (a API usa foto_google e, se vazia, foto).
        'foto' => mse_portal_ficha_url_foto($ficha['foto'] ?? ($ficha['foto_google'] ?? null)),
    ]];
}

/**
 * Departamento de verdade de uma ficha. Usa obras_departamento; se ele for
 * nome de obra (ex.: "CNPEM-FASEADA"), procura nos outros campos de
 * departamento/setor que a ficha possa trazer. Sem alternativa, devolve o
 * que veio (a Academy não inventa departamento).
 */
function mse_ficha_departamento(array $ficha): ?string
{
    if (!function_exists('mse_departamento_eh_obra')) {
        require_once __DIR__ . '/Departamentos.php';
    }
    $principal = isset($ficha['obras_departamento']) && is_scalar($ficha['obras_departamento'])
        ? trim((string) $ficha['obras_departamento']) : '';
    if ($principal !== '' && !mse_departamento_eh_obra($principal)) {
        return $principal;
    }
    foreach (['departamento', 'nome_departamento', 'departamento_nome', 'desc_departamento', 'setor', 'nome_setor',
              'setor_nome', 'area', 'nome_area', 'depto', 'lotacao'] as $campo) {
        $v = isset($ficha[$campo]) && is_scalar($ficha[$campo]) ? trim((string) $ficha[$campo]) : '';
        if ($v !== '' && !mse_departamento_eh_obra($v)) {
            return $v;
        }
    }
    return $principal !== '' ? $principal : null;
}

/**
 * Endereço da foto, só se for utilizável: http(s) completo ou caminho
 * começando em "/" (que vira endereço do Portal). Qualquer outra coisa
 * (vazio, "data:", "javascript:") é ignorada.
 */
function mse_portal_ficha_url_foto($valor): ?string
{
    $url = is_scalar($valor) ? trim((string) $valor) : '';
    if ($url === '') {
        return null;
    }
    if ($url[0] === '/' && strpos($url, '//') !== 0) {
        $url = 'https://portalmse.com.br' . $url;
    }
    return preg_match('#^https?://[^\s"\'<>]+$#i', $url) ? $url : null;
}

/**
 * Busca a ficha de UMA pessoa tentando, em ordem: CPF só com números, CPF
 * formatado (000.000.000-00) e nome completo. A API devolve o CPF
 * formatado e a busca só por números podia não achar ninguém — aí o
 * cargo e o departamento ficavam vazios mesmo com a ficha existindo.
 *
 * Achada pelo nome, a ficha só vale se o nome bater (e, havendo CPF dos
 * dois lados, o CPF também): senão um homônimo gravaria o cargo de outra
 * pessoa.
 *
 * @return array{ficha: ?array, erro: ?string}
 */
function mse_portal_ficha_buscar_pessoa(?string $cpf, ?string $nome): array
{
    $ultimoErro = null;
    foreach (mse_portal_ficha_tentativas($cpf, $nome) as $t) {
        $r = mse_portal_ficha_consultar($t['busca']);
        if ($r['erro'] !== null) {
            $ultimoErro = $r['erro'];
            // Token ou extensão faltando valem pra qualquer busca: não insiste.
            if (mse_portal_ficha_erro_geral($r['erro'])) {
                break;
            }
            continue;
        }
        if ($r['ficha'] !== null && mse_portal_ficha_confere($t, $r['ficha'], $cpf, $nome)) {
            return $r;
        }
    }
    return ['ficha' => null, 'erro' => $ultimoErro];
}

/** As buscas a tentar, em ordem: CPF só com números, CPF formatado, nome. */
function mse_portal_ficha_tentativas(?string $cpf, ?string $nome): array
{
    $digitos = preg_replace('/\D+/', '', (string) $cpf);
    $nome = trim((string) $nome);
    $tentativas = [];
    if (strlen($digitos) === 11) {
        $tentativas[] = ['busca' => $digitos, 'porNome' => false];
        $tentativas[] = ['busca' => substr($digitos, 0, 3) . '.' . substr($digitos, 3, 3) . '.'
            . substr($digitos, 6, 3) . '-' . substr($digitos, 9, 2), 'porNome' => false];
    }
    if ($nome !== '' && strpos($nome, '@') === false) {
        $tentativas[] = ['busca' => $nome, 'porNome' => true];
    }
    return $tentativas;
}

/** Erro que vale pra qualquer busca (sem token, sem curl): não adianta insistir. */
function mse_portal_ficha_erro_geral(string $erro): bool
{
    return strpos($erro, 'PORTAL_FICHA_API_TOKEN') !== false || strpos($erro, 'curl') !== false;
}

function mse_portal_ficha_normalizar_nome(string $t): string
{
    $t = mb_strtolower(trim($t), 'UTF-8');
    $semAcento = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
    if ($semAcento !== false) {
        $t = $semAcento;
    }
    return trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9\s]/', '', $t)));
}

/**
 * Achada pelo nome, a ficha só vale se o nome bater (e, havendo CPF dos
 * dois lados, o CPF também): senão um homônimo traria os dados de outro.
 */
function mse_portal_ficha_confere(array $tentativa, array $ficha, ?string $cpf, ?string $nome): bool
{
    if (!$tentativa['porNome']) {
        return true;
    }
    if (mse_portal_ficha_normalizar_nome((string) $ficha['nome']) !== mse_portal_ficha_normalizar_nome((string) $nome)) {
        return false;
    }
    $digitos = preg_replace('/\D+/', '', (string) $cpf);
    $cpfFicha = preg_replace('/\D+/', '', (string) ($ficha['cpf'] ?? ''));
    return !(strlen($digitos) === 11 && strlen((string) $cpfFicha) === 11 && $cpfFicha !== $digitos);
}

/**
 * A mesma busca de mse_portal_ficha_buscar_pessoa para muitas pessoas de
 * uma vez — usada no relatório de novos contratados. As consultas vão em
 * paralelo (até 8 ao mesmo tempo), em rodadas: quem não foi achado pelo
 * CPF só com números tenta o formatado, depois o nome.
 *
 * @param array<string, array{cpf: ?string, nome: ?string}> $pessoas  chave => quem buscar
 * @return array<string, array{ficha: ?array, bruta: ?array, erro: ?string}>  mesma chave
 */
function mse_portal_ficha_buscar_varias(array $pessoas): array
{
    $resultado = [];
    $filas = [];
    foreach ($pessoas as $chave => $p) {
        $resultado[$chave] = ['ficha' => null, 'bruta' => null, 'erro' => null];
        $filas[$chave] = mse_portal_ficha_tentativas($p['cpf'] ?? null, $p['nome'] ?? null);
    }

    while (true) {
        $rodada = [];
        foreach ($filas as $chave => $fila) {
            if ($fila) {
                $rodada[$chave] = array_shift($filas[$chave]);
            } else {
                unset($filas[$chave]);
            }
        }
        if (!$rodada) {
            break;
        }
        $respostas = mse_portal_ficha_consultar_varios(array_map(static fn($t) => $t['busca'], $rodada));
        foreach ($rodada as $chave => $t) {
            $r = $respostas[$chave];
            if ($r['erro'] !== null) {
                $resultado[$chave]['erro'] = $r['erro'];
                if (mse_portal_ficha_erro_geral($r['erro'])) {
                    foreach ($resultado as &$x) {
                        $x['erro'] = $x['ficha'] === null ? $r['erro'] : $x['erro'];
                    }
                    unset($x);
                    return $resultado;
                }
                continue;
            }
            if ($r['ficha'] !== null && mse_portal_ficha_confere($t, $r['ficha'], $pessoas[$chave]['cpf'] ?? null, $pessoas[$chave]['nome'] ?? null)) {
                $resultado[$chave] = ['ficha' => $r['ficha'], 'bruta' => $r['bruta'] ?? null, 'erro' => null];
                unset($filas[$chave]);
            }
        }
    }
    return $resultado;
}

/**
 * Várias consultas à API de ficha ao mesmo tempo (curl_multi), no máximo 8
 * abertas por vez pra não sobrecarregar a API.
 *
 * @param array<string, string> $termos  chave => termo de busca
 * @return array<string, array>  chave => mesmo formato de mse_portal_ficha_consultar
 */
function mse_portal_ficha_consultar_varios(array $termos): array
{
    if (!function_exists('curl_multi_init')) {
        $r = [];
        foreach ($termos as $chave => $termo) {
            $r[$chave] = mse_portal_ficha_consultar($termo);
        }
        return $r;
    }
    $baseUrl = rtrim(mse_env('PORTAL_FICHA_API_BASE', 'https://portalmse.com.br/microservices/hub_mse/api_ficha'), '/');
    $token = mse_env('PORTAL_FICHA_API_TOKEN', '');
    if ($token === '') {
        return array_map(static fn() => ['ficha' => null, 'erro' => 'PORTAL_FICHA_API_TOKEN não está configurado no .env do servidor.'], $termos);
    }

    $resultado = [];
    foreach (array_chunk($termos, 8, true) as $lote) {
        $multi = curl_multi_init();
        $handles = [];
        foreach ($lote as $chave => $termo) {
            $ch = curl_init($baseUrl . '/v1/ff_infos?' . http_build_query(['busca' => $termo]));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
            ]);
            curl_multi_add_handle($multi, $ch);
            $handles[$chave] = $ch;
        }
        do {
            $status = curl_multi_exec($multi, $ativos);
            if ($ativos) {
                curl_multi_select($multi, 1.0);
            }
        } while ($ativos && $status === CURLM_OK);

        foreach ($handles as $chave => $ch) {
            $resultado[$chave] = mse_portal_ficha_interpretar(
                curl_multi_getcontent($ch) ?? false,
                (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
                curl_error($ch)
            );
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($multi);
    }
    return $resultado;
}

/**
 * Guarda o departamento como vem do Portal (migração 023). Sem a coluna,
 * ou sem departamento na ficha, não faz nada.
 */
function mse_gravar_setor_portal(PDO $pdo, int $userId, ?string $setor): void
{
    $setor = trim((string) $setor);
    if ($setor === '' || $userId <= 0 || !mse_garantir_coluna_setor_portal($pdo)) {
        return;
    }
    $pdo->prepare('UPDATE users SET setor_portal = ? WHERE id = ?')->execute([mb_substr($setor, 0, 150), $userId]);
}

/**
 * Garante a coluna users.setor_portal (a mesma da migração 023). Se a
 * migração não rodou, cria aqui: é uma coluna vazia que não mexe em dado
 * nenhum, e sem ela o departamento do Portal não tinha onde ficar.
 * Devolve false se não existir e não der pra criar (sem permissão).
 */
function mse_garantir_coluna_setor_portal(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute(['users', 'setor_portal']);
    if ($stmt->fetchColumn() !== false) {
        return $ok = true;
    }
    try {
        $pdo->exec('ALTER TABLE users ADD COLUMN setor_portal VARCHAR(150) NULL');
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[setor_portal] Não consegui criar a coluna (rode a migração 023): ' . $e->getMessage());
        return $ok = false;
    }
}
