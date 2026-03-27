<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectDir = dirname(__DIR__);

require $projectDir.'/vendor/autoload.php';

if (!class_exists(Dotenv::class)) {
    return;
}

$dotenv = new Dotenv();
$dotenv->usePutenv(true);

$defaultEnvFile = $projectDir.'/.env';
$fallbackEnvFile = $projectDir.'/.env.example';
$testEnvFile = $projectDir.'/.env.test';

if (is_file($defaultEnvFile)) {
    $dotenv->loadEnv($defaultEnvFile);
} elseif (is_file($fallbackEnvFile)) {
    $dotenv->loadEnv($fallbackEnvFile);
}

$cliEnv = null;
if (\PHP_SAPI === 'cli' && isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
    foreach ($_SERVER['argv'] as $index => $arg) {
        if (str_starts_with($arg, '--env=')) {
            $cliEnv = substr($arg, 6);
            break;
        }

        if (in_array($arg, ['--env', '-e'], true)) {
            $cliEnv = $_SERVER['argv'][$index + 1] ?? null;
            break;
        }
    }
}

$resolvedEnv = $cliEnv
    ?? ($_SERVER['APP_ENV'] ?? null)
    ?? ($_ENV['APP_ENV'] ?? null)
    ?? (getenv('APP_ENV') ?: null);

if ('test' === $resolvedEnv && is_file($testEnvFile)) {
    $dotenv->overload($testEnvFile);
}
