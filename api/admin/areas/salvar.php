<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/Progress.php'; // mse_normalize_text()

mse_cors();
mse_require_admin();

/**
 * Cria um departamento ou muda o nome, o ícone e a descrição de um que já
 * existe.
 *
 * Sem "id", cria. Com "id", altera.
 *
 * O slug nunca muda depois de criado, mesmo quando o nome muda. Ele é a
 * ligação entre o cartão e os cursos ("cat" no front-end) e aparece em
 * cargo_areas e course_areas — trocar o slug quebraria essas ligações
 * silenciosamente, e o nome na tela é o que a pessoa realmente lê.
 *
 * Não há exclusão de propósito: apagar um departamento deixaria os vídeos
 * dele órfãos, sem nenhuma tela onde aparecer. Departamento sem vídeo
 * publicado simplesmente não é mostrado (ver api/areas/list.php), o que
 * resolve o caso real — tirar da frente o que está vazio — sem perder nada.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mse_error('Método não permitido.', 405);
}

$pdo = mse_db();

if ($pdo->query("SHOW COLUMNS FROM areas LIKE 'icon'")->fetch() === false) {
    mse_error('Este servidor ainda não tem as colunas de ícone e descrição. Falta rodar a migração 019 no banco (migrations/019_areas_icone_descricao.sql).', 409);
}

$input = mse_input();
$id = (int) ($input['id'] ?? 0);
$nome = trim((string) ($input['name'] ?? ''));
$icone = trim((string) ($input['icon'] ?? ''));
$descricao = trim((string) ($input['descricao'] ?? ''));

if ($nome === '') {
    mse_error('O nome do departamento não pode ficar em branco.', 422);
}
if (mb_strlen($nome) > 120) {
    mse_error('O nome do departamento passou de 120 caracteres.', 422);
}
if (mb_strlen($descricao) > 255) {
    mse_error('A descrição passou de 255 caracteres.', 422);
}

/**
 * Só ícones que a fonte do site realmente tem.
 *
 * O Font Awesome aqui é um recorte embutido no style.css, não a biblioteca
 * inteira — um nome de ícone que exista no site do Font Awesome mas não
 * neste recorte não desenha nada, e o cartão fica com um buraco no lugar.
 * Já aconteceu antes com seis ícones. Esta lista é a que o seletor da tela
 * mostra, conferida contra o style.css.
 */
const ICONES_DISPONIVEIS = [
    'fa-folder-open', 'fa-house', 'fa-building', 'fa-warehouse', 'fa-boxes-stacked',
    'fa-truck', 'fa-route', 'fa-diagram-project', 'fa-helmet-safety', 'fa-circle-exclamation',
    'fa-user', 'fa-user-plus', 'fa-handshake', 'fa-credit-card', 'fa-receipt',
    'fa-file-contract', 'fa-file-invoice', 'fa-file-lines', 'fa-file-signature',
    'fa-chart-line', 'fa-medal', 'fa-star', 'fa-book', 'fa-display', 'fa-magnifying-glass',
];

if ($icone === '') {
    $icone = 'fa-folder-open';
}
if (!in_array($icone, ICONES_DISPONIVEIS, true)) {
    mse_error('Esse ícone não existe na fonte do site. Escolha um da lista.', 422);
}

// ------------------------------------------------------------
// Alterar
// ------------------------------------------------------------
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT id, slug, name FROM areas WHERE id = ?');
    $stmt->execute([$id]);
    $area = $stmt->fetch();
    if (!$area) {
        mse_error('Departamento não encontrado.', 404);
    }

    // Dois departamentos com o mesmo nome deixam impossível saber qual é
    // qual na hora de cadastrar um vídeo.
    $stmt = $pdo->prepare('SELECT id FROM areas WHERE name = ? AND id <> ? LIMIT 1');
    $stmt->execute([$nome, $id]);
    if ($stmt->fetch()) {
        mse_error('Já existe outro departamento com esse nome.', 409);
    }

    $stmt = $pdo->prepare('UPDATE areas SET name = ?, icon = ?, descricao = ? WHERE id = ?');
    $stmt->execute([$nome, $icone, $descricao, $id]);

    mse_json([
        'id' => $id,
        'slug' => $area['slug'],
        'name' => $nome,
        'icon' => $icone,
        'descricao' => $descricao,
        'message' => $area['name'] === $nome
            ? 'Departamento atualizado.'
            : '"' . $area['name'] . '" agora se chama "' . $nome . '".',
    ]);
}

// ------------------------------------------------------------
// Criar
// ------------------------------------------------------------
$stmt = $pdo->prepare('SELECT id FROM areas WHERE name = ? LIMIT 1');
$stmt->execute([$nome]);
if ($stmt->fetch()) {
    mse_error('Já existe um departamento com esse nome.', 409);
}

// Slug a partir do nome: minúsculas, sem acento, espaços viram hífen.
$slug = mse_normalize_text($nome);
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim((string) $slug, '-');
if ($slug === '') {
    $slug = 'departamento';
}
if (mb_strlen($slug) > 60) {
    $slug = substr($slug, 0, 60);
}

// Nome diferente pode gerar o mesmo slug ("T.I." e "TI"). O slug é único
// na tabela, então sem isto o INSERT estouraria com erro de banco cru.
$base = $slug;
$n = 2;
$stmt = $pdo->prepare('SELECT id FROM areas WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
while ($stmt->fetch()) {
    $slug = substr($base, 0, 57) . '-' . $n;
    $n++;
    $stmt->execute([$slug]);
}

$ordem = (int) $pdo->query('SELECT COALESCE(MAX(ordem), 0) + 1 FROM areas')->fetchColumn();

$stmt = $pdo->prepare('INSERT INTO areas (slug, name, icon, descricao, ordem) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$slug, $nome, $icone, $descricao, $ordem]);

mse_json([
    'id' => (int) $pdo->lastInsertId(),
    'slug' => $slug,
    'name' => $nome,
    'icon' => $icone,
    'descricao' => $descricao,
    'total_cursos' => 0,
    'message' => 'Departamento "' . $nome . '" criado. Ele só aparece na seção Cursos depois que tiver pelo menos um vídeo publicado.',
], 201);
