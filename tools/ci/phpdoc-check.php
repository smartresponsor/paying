<?php

declare(strict_types=1);

$phar = 'tools/runtime/phpDocumentor.phar';
$vendorBin = DIRECTORY_SEPARATOR === '\\' ? 'vendor\\bin\\phpdoc.bat' : 'vendor/bin/phpdoc';

if (file_exists($phar)) {
    echo 'phpDocumentor PHAR present' . PHP_EOL;
    exit(0);
}

if (file_exists($vendorBin)) {
    echo 'phpDocumentor vendor binary present' . PHP_EOL;
    exit(0);
}

echo 'SKIP phpDocumentor tool missing' . PHP_EOL;
exit(0);
