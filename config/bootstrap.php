<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

/**
 * @param array<int, string> $argv
 */
function paymentResolveCliEnv(array $argv): ?string
{
    foreach ($argv as $index => $arg) {
        if (str_starts_with($arg, '--env=')) {
            return substr($arg, 6);
        }

        if (in_array($arg, ['--env', '-e'], true)) {
            return $argv[$index + 1] ?? null;
        }
    }

    return null;
}

function paymentResolveAppEnv(): ?string
{
    return $_SERVER['APP_ENV']
        ?? $_ENV['APP_ENV']
        ?? (getenv('APP_ENV') ?: null)
        ?? null;
}

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
    $cliEnv = paymentResolveCliEnv($_SERVER['argv']);
}

$resolvedEnv = $cliEnv ?? paymentResolveAppEnv();

if ('test' === $resolvedEnv && is_file($testEnvFile)) {
    $dotenv->overload($testEnvFile);
}
