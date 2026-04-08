<?php

declare(strict_types=1);

$phar = 'tools/runtime/doctum.phar';
$vendorBin = DIRECTORY_SEPARATOR === '\\' ? 'vendor\\bin\\doctum.bat' : 'vendor/bin/doctum';

if (file_exists($phar)) {
    echo 'Doctum PHAR present' . PHP_EOL;
    exit(0);
}

if (file_exists($vendorBin)) {
    echo 'Doctum vendor binary present' . PHP_EOL;
    exit(0);
}

echo 'SKIP Doctum tool missing' . PHP_EOL;
exit(0);
