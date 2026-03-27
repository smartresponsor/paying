<?php
declare(strict_types=1);
declare(strict_types=1);

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

use App\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? true);

$kernel = new Kernel($env, $debug);
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
try {
    $response = $kernel->handle($request);
} catch (Exception $e) {
}
$response->send();
$kernel->terminate($request, $response);
