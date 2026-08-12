<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

echo json_encode([
    'status' => 'ok',
    'runtime' => 'php',
    'php_version' => PHP_VERSION,
], JSON_THROW_ON_ERROR);
