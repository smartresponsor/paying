<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerFile = $root . DIRECTORY_SEPARATOR . 'composer.json';

$requiredScripts = [
    'report:canon-structure',
    'report:controller-name-form',
    'report:service-core-name-form',
    'report:api-boundary-name-form',
    'report:console-command-name-form',
    'report:infrastructure-name-form',
    'report:business-service-name-form',
    'report:service-adapter-name-form',
    'report:legacy-duplicate-retirement',
    'report:value-object-exception-name-form',
    'report:entity-first-persistence',
    'report:entity-first-consistency',
    'report:residual-legacy-duplicate-retirement',
    'report:webhook-controller-name-form',
    'report:provider-service-name-form',
    'report:canonical-name-form',
    'report:attribute-name-form',
    'report:subscriber-layer-name-form',
    'report:post-subscriber-residual-retirement',
    'report:inspection-script-registry',
    'report:canonical-structure-closure',
    'report:application-surface-name-form',
    'report:source-residual-name-form',
    'report:test-residual-name-form',
    'report:test-legacy-duplicate-retirement',
    'report:test-unmapped-residual-name-form',
    'report:test-unmapped-canonicalization',
    'report:test-canonical-closure',
    'report:release-candidate-structure',
    'report:packaging-root-surface',
    'report:paying-canonical-readiness',
    'report:composer-script-hygiene',
    'report:runtime-issue-inventory',
    'report:runtime-proof-core',
    'report:runtime-proof-closure',
    'report:rc3-milestone',
];

$errors = [];
$warnings = [];

if (!is_file($composerFile)) {
    echo "Paying composer script hygiene report\n";
    echo "=====================================\n";
    echo "Status: FAILED\n";
    echo "- Missing composer.json\n";
    exit(1);
}

$contents = (string) file_get_contents($composerFile);
if (str_starts_with($contents, "\xEF\xBB\xBF")) {
    $errors[] = 'composer.json contains UTF-8 BOM.';
}

$composer = json_decode($contents, true);
if (!is_array($composer)) {
    echo "Paying composer script hygiene report\n";
    echo "=====================================\n";
    echo "Status: FAILED\n";
    echo "- composer.json is not valid JSON\n";
    exit(1);
}

$scripts = isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

foreach ($requiredScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $errors[] = 'Missing required composer script: ' . $scriptName;
    }
}

foreach ($scripts as $scriptName => $definition) {
    if (!is_string($scriptName) || !str_starts_with($scriptName, 'report:')) {
        continue;
    }

    if (!is_array($definition) && !is_string($definition)) {
        continue;
    }

    $definitionText = is_array($definition) ? implode("\n", array_map('strval', $definition)) : $definition;

    if (str_contains($definitionText, 'tools/inspection/') && !str_contains($definitionText, 'tools/php/php84.php')) {
        $warnings[] = 'Report script does not use tools/php/php84.php wrapper: ' . $scriptName;
    }
}

echo "Paying composer script hygiene report\n";
echo "=====================================\n";
echo 'Required scripts: ' . count($requiredScripts) . "\n";
echo 'Available composer scripts: ' . count($scripts) . "\n";
echo 'Warnings: ' . count($warnings) . "\n";
echo 'Errors: ' . count($errors) . "\n";

foreach ($warnings as $warning) {
    echo '[WARN] ' . $warning . "\n";
}

if ($errors !== []) {
    echo "Status: FAILED\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
