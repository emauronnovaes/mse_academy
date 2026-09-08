<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/Cors.php';
require_once __DIR__ . '/../../../src/Response.php';
require_once __DIR__ . '/../../../src/Auth.php';
require_once __DIR__ . '/../../../src/AwsS3.php';

mse_cors();
mse_require_admin();

$prefix = trim((string) ($_GET['prefix'] ?? ''));

try {
    $items = mse_s3_list_objects($prefix);
} catch (RuntimeException $e) {
    mse_error($e->getMessage(), 502);
}

mse_json(['prefix' => $prefix, 'items' => $items, 'total' => count($items)]);
