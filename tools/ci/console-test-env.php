<?php

declare(strict_types=1);

require_once __DIR__.'/../runtime/payment_runtime.php';

/**
 * @param array<string, string> $env
 */
function paymentExportEnv(array $env): void
{
    foreach ($env as $name => $value) {
        putenv($name.'='.$value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

/**
 * @param list<string> $escapedArgs
 */
function paymentBuildLocalConsoleCommand(array $escapedArgs): string
{
    return escapeshellarg(PHP_BINARY).' '.escapeshellarg('bin/console').' '.implode(' ', $escapedArgs).' --env=test';
}

if ($argc < 2) {
    fwrite(STDERR, 'Console command is required' . PHP_EOL);
    exit(1);
}

$env = [
    'APP_ENV' => 'test',
    'APP_DEBUG' => '0',
    'APP_SECRET' => 'payment_test_secret',
    'DATABASE_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.data.sqlite',
    'INFRA_URL' => 'sqlite:///%kernel.project_dir%/var/payment.test.infra.sqlite',
    'STRIPE_WEBHOOK_SECRET' => 'payment_test_whsec',
    'PAYMENT_TEST_QUARANTINE_LEGACY' => '0',
];

paymentExportEnv($env);

$args = array_slice($argv, 1);
$escaped = array_map(static fn(string $arg): string => escapeshellarg($arg), $args);

$shouldUseDocker = paymentShouldUseDockerRuntime(['pdo_sqlite', 'pdo_pgsql'], 'var/cache/test');

if ($shouldUseDocker) {
    try {
        $dockerCommand = paymentDockerPhpCommand(dirname(__DIR__, 2), 'bin/console', $args);
    } catch (RuntimeException $exception) {
        fwrite(STDERR, $exception->getMessage().PHP_EOL);
        exit(1);
    }

    $dockerEnv = array_map(
        static fn (string $name, string $value): string => '-e '.escapeshellarg($name.'='.$value),
        array_keys($env),
        array_values($env),
    );

    $command = implode(' ', array_filter([
        implode(' ', array_map(static fn (string $part): string => escapeshellarg($part), array_slice($dockerCommand, 0, 5))),
        implode(' ', $dockerEnv),
        implode(' ', array_map(static fn (string $part): string => escapeshellarg($part), array_slice($dockerCommand, 5))),
        '--env=test',
    ]));
} else {
    $command = paymentBuildLocalConsoleCommand($escaped);
}

passthru($command, $exitCode);
exit($exitCode);
