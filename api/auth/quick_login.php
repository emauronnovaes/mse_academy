<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Cors.php';
require_once __DIR__ . '/../../src/Response.php';

mse_cors();
mse_error(
    'Login direto por e-mail foi desativado. Acesse a MSE Academy pelo Portal MSE.',
    410
);
