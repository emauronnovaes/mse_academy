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

// Áreas cobertas = quantas áreas já têm pelo menos 1 curso do catálogo
// publicado (área sem nenhum curso ainda não conta como "coberta").
$areas = (int) $pdo->query(
    "SELECT COUNT(DISTINCT area_id) AS c FROM courses WHERE type = 'curso' AND is_published = 1 AND area_id IS NOT NULL"
)->fetch()['c'];

mse_json([
    'colaboradores_atendidos' => $colaboradores,
    'tutoriais_disponiveis' => $tutoriais,
    'areas_cobertas' => $areas,
]);
