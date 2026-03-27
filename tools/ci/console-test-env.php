<?php

declare(strict_types=1);

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

foreach ($env as $name => $value) {
    putenv($name . '=' . $value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

$args = array_slice($argv, 1);
$escaped = array_map(static fn(string $arg): string => escapeshellarg($arg), $args);

$shouldUseDocker = !extension_loaded('pdo_sqlite')
    || !extension_loaded('pdo_pgsql')
    || (is_dir('var/cache/test') && !is_writable('var/cache/test'));

if ($shouldUseDocker) {
    $dockerEnv = array_map(
        static fn (string $name, string $value): string => '-e '.escapeshellarg($name.'='.$value),
        array_keys($env),
        array_values($env),
    );

    $command = implode(' ', array_filter([
        'docker compose run --rm -T',
        '-e PAYMENT_BOOTSTRAP_SCHEMA=0',
        implode(' ', $dockerEnv),
        'app',
        'php',
        escapeshellarg('bin/console'),
        implode(' ', $escaped),
        '--env=test',
    ]));
} else {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg('bin/console') . ' ' . implode(' ', $escaped) . ' --env=test';
}

passthru($command, $exitCode);
exit($exitCode);
