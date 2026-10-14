<?php
declare(strict_types=1);

require_once __DIR__ . '/Progress.php'; // mse_normalize_text(), mse_tem_coluna()

/**
 * Classificação do vídeo, escolhida no "Adicionar vídeo":
 *
 *   palavras_chave   pelo menos 3, separadas por vírgula; entram na busca.
 *   obrigatoriedade  'recomendado'   -> recomendado para os departamentos relacionados
 *                                      (ou para todos, se nenhum foi marcado);
 *                    'departamentos' -> obrigatório para os departamentos relacionados;
 *                    'todos'         -> obrigatório para todos os colaboradores.
 *                    NULL (vídeos antigos) -> obrigatório só para os departamentos marcados.
 *   tema_sugerido    assunto novo sugerido por quem enviou ("Outro" no Tema);
 *   tema_status      'pendente' | 'aprovado' | 'recusado' (decidido na aprovação).
 *
 * Só aulas OBRIGATÓRIAS contam nos relatórios do banco (consultasmia);
 * recomendadas só aparecem para a pessoa.
 *
 * Colunas da migração 040. Se ela não rodou, são criadas aqui (colunas
 * vazias, não mexem em dado nenhum).
 */

const MSE_COLUNAS_CLASSIFICACAO = [
    'palavras_chave' => 'VARCHAR(1000) NULL',
    'obrigatoriedade' => 'VARCHAR(20) NULL',
    'tema_sugerido' => 'VARCHAR(120) NULL',
    'tema_status' => 'VARCHAR(10) NULL',
    // Vídeo de integração só para quem é de obra (não alocado na sede).
    'somente_obra' => 'TINYINT(1) NOT NULL DEFAULT 0',
];

const MSE_OBRIGATORIEDADES = ['recomendado', 'departamentos', 'todos'];

/** As colunas existem (ou acabaram de ser criadas)? Fora de transação (ALTER encerra a transação). */
function mse_garantir_colunas_classificacao(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute(['courses']);
    $faltando = array_diff_key(MSE_COLUNAS_CLASSIFICACAO, array_flip(array_column($stmt->fetchAll(), 'COLUMN_NAME')));
    if (!$faltando) {
        return $ok = true;
    }
    try {
        $partes = [];
        foreach ($faltando as $nome => $tipo) {
            $partes[] = "ADD COLUMN {$nome} {$tipo}";
        }
        $pdo->exec('ALTER TABLE courses ' . implode(', ', $partes));
        return $ok = true;
    } catch (Throwable $e) {
        error_log('[classificacao] Não consegui criar as colunas (rode a migração 040): ' . $e->getMessage());
        return $ok = false;
    }
}

/**
 * Palavras-chave do corpo (lista ou texto separado por vírgula), limpas e sem
 * repetição. null = não veio no corpo. Com $exigir, pede pelo menos 3.
 */
function mse_ler_palavras_chave(array $input, bool $exigir): ?string
{
    if (!array_key_exists('palavras_chave', $input)) {
        if ($exigir) {
            mse_error('Escreva pelo menos 3 palavras-chave (as palavras que as pessoas usariam para buscar o vídeo).', 422);
        }
        return null;
    }
    $bruto = $input['palavras_chave'];
    $lista = is_array($bruto) ? $bruto : explode(',', (string) $bruto);
    $palavras = [];
    foreach ($lista as $p) {
        $p = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', (string) $p)));
        if ($p === '') {
            continue;
        }
        if (mb_strlen($p) > 40) {
            mse_error('Palavra-chave longa demais: "' . mb_substr($p, 0, 40) . '..." (máximo 40 letras cada).', 422);
        }
        $palavras[mse_normalize_text($p)] ??= $p; // repetida: fica a primeira forma digitada
    }
    if (count($palavras) > 20) {
        mse_error('No máximo 20 palavras-chave.', 422);
    }
    if ($exigir && count($palavras) < 3) {
        mse_error('Escreva pelo menos 3 palavras-chave (as palavras que as pessoas usariam para buscar o vídeo).', 422);
    }
    return $palavras ? implode(', ', $palavras) : null;
}

/** 'recomendado' | 'departamentos' | 'todos'. null = não veio. Com $exigir, é obrigatório escolher. */
function mse_ler_obrigatoriedade(array $input, bool $exigir): ?string
{
    $v = isset($input['obrigatoriedade']) ? (string) $input['obrigatoriedade'] : '';
    if ($v === '') {
        if ($exigir) {
            mse_error('Escolha se o vídeo é recomendado ou obrigatório.', 422);
        }
        return null;
    }
    if (!in_array($v, MSE_OBRIGATORIEDADES, true)) {
        mse_error('obrigatoriedade precisa ser "recomendado", "departamentos" ou "todos".', 422);
    }
    return $v;
}

/**
 * Para esta pessoa, a aula é 'obrigatoria', 'recomendada' ou nada (null).
 *
 * @param array $deptosDoCurso  chaves normalizadas dos departamentos relacionados ao curso
 * @param array $meusDeptos     chaves normalizadas dos departamentos da pessoa
 */
function mse_nivel_da_aula(?string $obrigatoriedade, array $deptosDoCurso, array $meusDeptos): ?string
{
    $doMeuDepto = (bool) array_intersect_key($deptosDoCurso, $meusDeptos);
    if ($obrigatoriedade === 'todos') {
        return 'obrigatoria';
    }
    if ($obrigatoriedade === 'recomendado') {
        return (!$deptosDoCurso || $doMeuDepto) ? 'recomendada' : null;
    }
    // 'departamentos' e vídeos antigos (NULL): obrigatória para quem é dos departamentos marcados.
    return $doMeuDepto ? 'obrigatoria' : null;
}

/**
 * Acha o assunto pelo nome (sem diferenciar maiúsculas e acentos) ou cria um
 * novo, com o mesmo slug que a tela de Assuntos geraria. Devolve o id.
 */
function mse_criar_ou_achar_assunto(PDO $pdo, string $nome): int
{
    $nome = mb_substr(trim(preg_replace('/\s+/', ' ', $nome)), 0, 120);
    if ($nome === '') {
        mse_error('Informe o nome do tema.', 422);
    }
    $chave = mse_normalize_text($nome);
    foreach ($pdo->query('SELECT id, name FROM areas') as $a) {
        if (mse_normalize_text((string) $a['name']) === $chave) {
            return (int) $a['id'];
        }
    }
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $chave), '-');
    $slug = substr($slug !== '' ? $slug : 'assunto', 0, 60);
    $base = $slug;
    $existe = $pdo->prepare('SELECT 1 FROM areas WHERE slug = ? LIMIT 1');
    for ($n = 2; ; $n++) {
        $existe->execute([$slug]);
        if ($existe->fetchColumn() === false) {
            break;
        }
        $slug = substr($base, 0, 57) . '-' . $n;
    }
    if (mse_tem_coluna($pdo, 'areas', 'icon')) {
        $ordem = (int) $pdo->query('SELECT COALESCE(MAX(ordem), 0) + 1 FROM areas')->fetchColumn();
        $pdo->prepare('INSERT INTO areas (slug, name, icon, ordem) VALUES (?, ?, ?, ?)')->execute([$slug, $nome, 'fa-folder-open', $ordem]);
    } else {
        $pdo->prepare('INSERT INTO areas (slug, name) VALUES (?, ?)')->execute([$slug, $nome]);
    }
    return (int) $pdo->lastInsertId();
}
