<?php

declare(strict_types=1);

$required = [
    'src/Kernel.php',
    'src/Service/PaymentService.php',
    'src/Controller/PaymentConsoleController.php',
    'config/services.yaml',
];
$missing = array_values(array_filter($required, static fn(string $file): bool => !file_exists($file)));
if ($missing !== []) {
    fwrite(STDERR, 'Missing runtime proof files: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}
echo 'Payment runtime smoke passed.' . PHP_EOL;
