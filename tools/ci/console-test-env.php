<?php

declare(strict_types=1);

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
 * @param array<string, string> $env
 */
function paymentBuildDockerConsoleCommand(array $escapedArgs, array $env): string
{
    $dockerEnv = array_map(
        static fn (string $name, string $value): string => '-e '.escapeshellarg($name.'='.$value),
        array_keys($env),
        array_values($env),
    );

    return implode(' ', array_filter([
        'docker compose run --rm -T',
        '-e PAYMENT_BOOTSTRAP_SCHEMA=0',
        implode(' ', $dockerEnv),
        'app',
        'php',
        escapeshellarg('bin/console'),
        implode(' ', $escapedArgs),
        '--env=test',
    ]));
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

$shouldUseDocker = !extension_loaded('pdo_sqlite')
    || !extension_loaded('pdo_pgsql')
    || (is_dir('var/cache/test') && !is_writable('var/cache/test'));

if ($shouldUseDocker) {
    $command = paymentBuildDockerConsoleCommand($escaped, $env);
} else {
    $command = paymentBuildLocalConsoleCommand($escaped);
}

passthru($command, $exitCode);
exit($exitCode);
