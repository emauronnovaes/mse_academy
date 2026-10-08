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

    return ['erro' => null, 'campos' => array_keys($ficha), 'ficha' => [
        'nome' => (string) ($ficha['nome'] ?? ''),
        'cpf' => isset($ficha['cpf']) ? preg_replace('/\D/', '', (string) $ficha['cpf']) : null,
        // "funcao" na ficha é o cargo oficial do RH — mais confiável que
        // o que o token do Portal manda (se mandar).
        'funcao' => $cargo,
        'obras_departamento' => isset($ficha['obras_departamento']) ? trim((string) $ficha['obras_departamento']) : null,
        // Documentação não lista "email" entre os campos, mas alguns
        // registros trazem — pegamos se vier, sem depender disso.
        'email' => isset($ficha['email']) && $ficha['email'] !== '' ? strtolower(trim((string) $ficha['email'])) : null,
    ]];
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

    $normalizar = static function (string $t): string {
        $t = mb_strtolower(trim($t), 'UTF-8');
        $semAcento = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
        if ($semAcento !== false) {
            $t = $semAcento;
        }
        return trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9\s]/', '', $t)));
    };

    $ultimoErro = null;
    foreach ($tentativas as $t) {
        $r = mse_portal_ficha_consultar($t['busca']);
        if ($r['erro'] !== null) {
            $ultimoErro = $r['erro'];
            // Token ou extensão faltando valem pra qualquer busca: não insiste.
            if (strpos($r['erro'], 'PORTAL_FICHA_API_TOKEN') !== false || strpos($r['erro'], 'curl') !== false) {
                break;
            }
            continue;
        }
        $ficha = $r['ficha'];
        if ($ficha === null) {
            continue;
        }
        if ($t['porNome']) {
            if ($normalizar((string) $ficha['nome']) !== $normalizar($nome)) {
                continue;
            }
            $cpfFicha = preg_replace('/\D+/', '', (string) ($ficha['cpf'] ?? ''));
            if (strlen($digitos) === 11 && strlen((string) $cpfFicha) === 11 && $cpfFicha !== $digitos) {
                continue;
            }
        }
        return $r;
    }
    return ['ficha' => null, 'erro' => $ultimoErro];
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
