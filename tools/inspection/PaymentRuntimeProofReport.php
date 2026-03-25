<?php

declare(strict_types=1);

$proof = [
    'kernel' => file_exists('src/Kernel.php'),
    'services_yaml' => file_exists('config/services.yaml'),
    'phpunit' => file_exists('phpunit.xml.dist'),
    'openapi' => file_exists('docs/api/openapi.yaml'),
    'pipeline_runner_ps1' => file_exists('tools/ci/run-payment-local-pipeline.ps1'),
];
$targetDir = 'var/report/inspection';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$target = $targetDir . '/payment-runtime-proof.json';
file_put_contents($target, json_encode($proof, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
echo $target . PHP_EOL;
