<?php

declare(strict_types=1);

array_shift($argv);
if ($argv === []) {
    fwrite(STDERR, "Usage: php tools/php/php84.php <target> [args...]\n");
    exit(1);
}
$target = array_shift($argv);
$cmd = array_merge([PHP_BINARY, $target], $argv);
$descriptorSpec = [0 => STDIN, 1 => STDOUT, 2 => STDERR];
$process = proc_open($cmd, $descriptorSpec, $pipes, getcwd(), $_ENV);
if (!is_resource($process)) {
    fwrite(STDERR, "Unable to start PHP target process.\n");
    exit(1);
}
$status = proc_close($process);
exit(is_int($status) ? $status : 1);
