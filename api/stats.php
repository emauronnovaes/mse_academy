<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Cors.php';
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../config/database.php';

mse_cors();

/**
 * Estatísticas reais da tela inicial — endpoint PÚBLICO de propósito
 * (sem exigir login), já que essas informações aparecem pra qualquer
 * um antes mesmo de entrar. Nada aqui expõe dado sensível: só
 * contagens agregadas (nunca nomes, e-mails, etc).
 */

$pdo = mse_db();

// Colaboradores atendidos = pessoas distintas que já acessaram alguma
// vez (contando só quem realmente entrou, não cadastros vazios).
$colaboradores = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM users WHERE active = 1 AND distinct_access_count > 0"
)->fetch()['c'];

// Tutoriais disponíveis = total de cursos publicados (integração + catálogo).
$tutoriais = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM courses WHERE is_published = 1"
)->fetch()['c'];

// Aulas assistidas = quantas aulas foram concluídas, somando todo mundo.
//
// Substituiu "áreas do portal cobertas", que contava departamentos com
// pelo menos um vídeo. Esse número não dizia nada a quem chega: ele mede
// o quanto o catálogo foi preenchido, não o quanto a Academy é usada —
// e fica parado em um ou dois por muito tempo, parecendo que nada
// acontece. Este cresce toda vez que alguém termina uma aula.
$aulasAssistidas = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM user_course_progress WHERE status = 'concluido'"
)->fetch()['c'];

mse_json([
    'colaboradores_atendidos' => $colaboradores,
    'tutoriais_disponiveis' => $tutoriais,
    'aulas_assistidas' => $aulasAssistidas,
]);
