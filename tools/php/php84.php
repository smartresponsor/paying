<?php

declare(strict_types=1);

require_once __DIR__.'/../runtime/payment_runtime.php';

const PAYMENT_REQUIRED_EXTENSIONS = [
    'pdo_sqlite',
    'pdo_pgsql',
];

array_shift($argv);
if ($argv === []) {
    fwrite(STDERR, "Usage: php tools/php/php84.php <target> [args...]\n");
    exit(1);
}

$target = array_shift($argv);
$cmd = array_merge([PHP_BINARY, $target], $argv);

if (paymentShouldUseDockerRuntime(PAYMENT_REQUIRED_EXTENSIONS, 'var/cache/test')) {
    try {
        $dockerCmd = paymentDockerPhpCommand((string) getcwd(), $target, $argv);
    } catch (RuntimeException $exception) {
        fwrite(STDERR, $exception->getMessage()."\n");
        exit(1);
    }

    exit(paymentRunProcess($dockerCmd));
}

exit(paymentRunProcess($cmd));
