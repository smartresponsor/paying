<?php

declare(strict_types=1);

$files = ['var/report/local/latest/summary.md', 'var/report/local/latest/report.json'];
foreach ($files as $file) {
    echo (file_exists($file) ? 'OK   ' : 'MISS ') . $file . PHP_EOL;
}
