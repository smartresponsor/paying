<?php

declare(strict_types=1);

const PAYMENT_REQUIRED_EXTENSIONS = [
    'pdo_sqlite',
    'pdo_pgsql',
];

array_shift($argv);
if ($argv === []) {
    fwrite(STDERR, "Usage: php tools/php/php84.php <target> [args...]\n");
    exit(1);
}

$shouldUseDocker = static function (): bool {
    $runtime = $_ENV['PAYMENT_PHP_RUNTIME'] ?? $_SERVER['PAYMENT_PHP_RUNTIME'] ?? null;
    if ('local' === $runtime) {
        return false;
    }

    if ('docker' === $runtime) {
        return true;
    }

    foreach (PAYMENT_REQUIRED_EXTENSIONS as $extension) {
        if (!extension_loaded($extension)) {
            return true;
        }
    }

    return false;
};

$runCommand = static function (array $cmd): int {
    $descriptorSpec = [0 => STDIN, 1 => STDOUT, 2 => STDERR];
    $process = proc_open($cmd, $descriptorSpec, $pipes, getcwd(), $_ENV);
    if (!is_resource($process)) {
        fwrite(STDERR, "Unable to start PHP target process.\n");
        return 1;
    }

    $status = proc_close($process);
    return is_int($status) ? $status : 1;
};

$target = array_shift($argv);
$cmd = array_merge([PHP_BINARY, $target], $argv);

if ($shouldUseDocker()) {
    $composeFile = getcwd().'/compose.yml';
    if (!is_file($composeFile)) {
        fwrite(STDERR, "Docker fallback requested, but compose.yml was not found.\n");
        exit(1);
    }

    $dockerCmd = array_merge(
        ['docker', 'compose', 'run', '--rm', '-T', '-e', 'PAYMENT_BOOTSTRAP_SCHEMA=0', 'app', 'php', $target],
        $argv
    );

    exit($runCommand($dockerCmd));
}

exit($runCommand($cmd));
