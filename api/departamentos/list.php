<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Departamentos.php';

/**
 * Departamentos do Portal (Programação, Administrativo...), para o campo
 * "Obrigatório para quais departamentos?" do formulário de vídeo. Qualquer
 * pessoa logada pode ler: quem envia vídeo para aprovação também escolhe.
 *
 * GET
 */

mse_cors();
mse_require_auth();

$r = mse_departamentos_portal(mse_db());
mse_json([
    'departamentos' => $r['departamentos'],
    'fonte' => $r['fonte'],
    'aviso' => $r['aviso'],
]);
