<?php

declare(strict_types=1);

$required = ['composer.json', '.env.example', '.env.test', 'phpunit.xml.dist'];
foreach ($required as $file) {
    if (!file_exists($file)) {
        fwrite(STDERR, $file . ' missing' . PHP_EOL);
        exit(1);
    }
}

echo 'Install preflight files present' . PHP_EOL;
