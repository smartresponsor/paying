<?php

declare(strict_types=1);

$file = 'docs/api/openapi.yaml';
if (!file_exists($file)) {
    fwrite(STDERR, $file . ' not found' . PHP_EOL);
    exit(1);
}
$contents = (string) file_get_contents($file);
if (!str_contains($contents, 'openapi:')) {
    fwrite(STDERR, $file . ' does not contain openapi: header' . PHP_EOL);
    exit(1);
}
echo 'OpenAPI document present: ' . $file . PHP_EOL;
