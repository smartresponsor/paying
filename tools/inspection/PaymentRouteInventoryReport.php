<?php

declare(strict_types=1);

$routes = glob('config/routes/*.yaml') ?: [];
$rows = ["file,route_count"];
foreach ($routes as $routeFile) {
    $contents = (string) file_get_contents($routeFile);
    preg_match_all('/^\w[\w.-]*:\s*$/m', $contents, $matches);
    $rows[] = basename($routeFile) . ',' . count($matches[0]);
}
$targetDir = 'var/report/inspection';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$target = $targetDir . '/payment-route-inventory.csv';
file_put_contents($target, implode(PHP_EOL, $rows) . PHP_EOL);
echo $target . PHP_EOL;
