<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root.'/composer.json';

$requiredScripts = [
    'report:canon-structure' => 'tools/inspection/PayingCanonicalStructureAudit.php',
    'report:controller-nameEntity-form' => 'tools/inspection/PayingControllerNameFormReport.php',
    'report:service-core-nameEntity-form' => 'tools/inspection/PayingServiceCoreNameFormReport.php',
    'report:api-boundary-nameEntity-form' => 'tools/inspection/PayingApiBoundaryNameFormReport.php',
    'report:console-command-nameEntity-form' => 'tools/inspection/PayingConsoleCommandNameFormReport.php',
    'report:infrastructure-nameEntity-form' => 'tools/inspection/PayingInfrastructureNameFormReport.php',
    'report:business-service-nameEntity-form' => 'tools/inspection/PayingBusinessServiceNameFormReport.php',
    'report:service-adapter-nameEntity-form' => 'tools/inspection/PayingServiceAdapterNameFormReport.php',
    'report:legacy-duplicate-retirement' => 'tools/inspection/PayingLegacyDuplicateRetirementReport.php',
    'report:value-object-exception-nameEntity-form' => 'tools/inspection/PayingValueObjectExceptionNameFormReport.php',
    'report:entity-first-persistence' => 'tools/inspection/PayingEntityFirstPersistenceReport.php',
    'report:residual-legacy-duplicate-retirement' => 'tools/inspection/PayingResidualLegacyDuplicateRetirementReport.php',
    'report:webhook-controller-nameEntity-form' => 'tools/inspection/PayingWebhookControllerNameFormReport.php',
    'report:provider-service-nameEntity-form' => 'tools/inspection/PayingProviderServiceNameFormReport.php',
    'report:canonical-nameEntity-form' => 'tools/inspection/PayingCanonicalNameFormSummaryReport.php',
    'report:attribute-nameEntity-form' => 'tools/inspection/PayingAttributeNameFormReport.php',
    'report:subscriber-layer-nameEntity-form' => 'tools/inspection/PayingSubscriberLayerNameFormReport.php',
    'report:post-subscriber-residual-retirement' => 'tools/inspection/PayingPostSubscriberResidualRetirementReport.php',
    'report:inspection-script-registry' => 'tools/inspection/PayingInspectionScriptRegistryReport.php',
];

$failures = [];
if (!is_file($composerPath)) {
    $failures[] = 'Missing composer.json.';
} else {
    $composer = json_decode((string) file_get_contents($composerPath), true);
    if (!is_array($composer)) {
        $failures[] = 'composer.json is not valid JSON.';
    } else {
        $scripts = $composer['scripts'] ?? [];
        if (!is_array($scripts)) {
            $failures[] = 'composer.json scripts section is missing or not an object.';
        } else {
            foreach ($requiredScripts as $scriptName => $reportPath) {
                $expectedCommand = '@php tools/php/php84.php '.$reportPath;
                $actualCommand = $scripts[$scriptName] ?? null;
                if ($actualCommand !== $expectedCommand) {
                    $failures[] = sprintf(
                        'Missing or incorrect composer script %s. Expected: %s',
                        $scriptName,
                        $expectedCommand,
                    );
                }
            }
        }
    }
}

foreach ($requiredScripts as $scriptName => $reportPath) {
    if (!is_file($root.'/'.$reportPath)) {
        $failures[] = sprintf('Composer report %s points to missing file: %s', $scriptName, $reportPath);
    }
}

if ([] !== $failures) {
    echo "Paying inspection script registry report: FAIL\n";
    foreach ($failures as $failure) {
        echo '- '.$failure."\n";
    }
    exit(1);
}

echo "Paying inspection script registry report: OK\n";
exit(0);
