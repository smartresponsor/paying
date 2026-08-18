<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function paying_path(string $root, string $relative): string
{
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
}

function paying_collect_php_files(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile() || strtolower($item->getExtension()) !== 'php') {
            continue;
        }

        $files[] = $item->getPathname();
    }

    sort($files);

    return $files;
}

function paying_relative(string $root, string $absolute): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $normalizedAbsolute = str_replace('\\', '/', $absolute);

    if (str_starts_with($normalizedAbsolute, $normalizedRoot)) {
        return substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    return $normalizedAbsolute;
}

function paying_find_basename(string $root, string $basename): ?string
{
    foreach (paying_collect_php_files(paying_path($root, 'src')) as $file) {
        if (basename($file) === $basename) {
            return paying_relative($root, $file);
        }
    }

    return null;
}

$errors = [];
$warnings = [];

$requiredReports = [
    'tools/inspection/PayingCanonicalStructureAudit.php',
    'tools/inspection/PayingControllerNameFormReport.php',
    'tools/inspection/PayingServiceCoreNameFormReport.php',
    'tools/inspection/PayingApiBoundaryNameFormReport.php',
    'tools/inspection/PayingConsoleCommandNameFormReport.php',
    'tools/inspection/PayingInfrastructureNameFormReport.php',
    'tools/inspection/PayingBusinessServiceNameFormReport.php',
    'tools/inspection/PayingServiceAdapterNameFormReport.php',
    'tools/inspection/PayingLegacyDuplicateRetirementReport.php',
    'tools/inspection/PayingValueObjectExceptionNameFormReport.php',
    'tools/inspection/PayingEntityFirstPersistenceReport.php',
    'tools/inspection/PayingResidualLegacyDuplicateRetirementReport.php',
    'tools/inspection/PayingWebhookControllerNameFormReport.php',
    'tools/inspection/PayingProviderServiceNameFormReport.php',
    'tools/inspection/PayingCanonicalNameFormSummaryReport.php',
    'tools/inspection/PayingAttributeNameFormReport.php',
    'tools/inspection/PayingSubscriberLayerNameFormReport.php',
    'tools/inspection/PayingPostSubscriberResidualRetirementReport.php',
];

foreach ($requiredReports as $report) {
    if (!is_file(paying_path($root, $report))) {
        $errors[] = 'Missing required report: ' . $report;
    }
}

$composerFile = paying_path($root, 'composer.json');
$composer = is_file($composerFile) ? json_decode((string) file_get_contents($composerFile), true) : null;
$scripts = is_array($composer) && isset($composer['scripts']) && is_array($composer['scripts']) ? $composer['scripts'] : [];

$requiredComposerScripts = [
    'report:canon-structure',
    'report:controller-nameEntity-form',
    'report:service-core-nameEntity-form',
    'report:api-boundary-nameEntity-form',
    'report:console-command-nameEntity-form',
    'report:infrastructure-nameEntity-form',
    'report:business-service-nameEntity-form',
    'report:service-adapter-nameEntity-form',
    'report:legacy-duplicate-retirement',
    'report:value-object-exception-nameEntity-form',
    'report:entity-first-persistence',
    'report:residual-legacy-duplicate-retirement',
    'report:webhook-controller-nameEntity-form',
    'report:provider-service-nameEntity-form',
    'report:canonical-nameEntity-form',
    'report:attribute-nameEntity-form',
    'report:subscriber-layer-nameEntity-form',
    'report:post-subscriber-residual-retirement',
    'report:canonical-structure-closure',
];

foreach ($requiredComposerScripts as $scriptName) {
    if (!array_key_exists($scriptName, $scripts)) {
        $errors[] = 'Missing required composer script: ' . $scriptName;
    }
}

$expectedCanonicalBasenames = [
    'PaymentStartController.php',
    'PaymentFinalizeController.php',
    'PaymentStatusController.php',
    'PaymentWebhookController.php',
    'PaymentMetricController.php',
    'PaymentDlqController.php',
    'PaymentInternalProvider.php',
    'PaymentPayPalProvider.php',
    'PaymentStripeProvider.php',
    'PaymentMoney.php',
    'PaymentRequireScopeAttribute.php',
    'PaymentMetricSubscriber.php',
    'PaymentRateLimitSubscriber.php',
    'PaymentResponseHeaderSubscriber.php',
    'PaymentScopeGuardSubscriber.php',
];

$resolvedCanonicalPaths = [];
foreach ($expectedCanonicalBasenames as $basename) {
    $found = paying_find_basename($root, $basename);
    if ($found === null) {
        $errors[] = 'Missing expected canonical class file by basename: ' . $basename;
        continue;
    }

    $resolvedCanonicalPaths[$basename] = $found;
}

$forbiddenLegacyBasenames = [
    'StartController.php',
    'FinalizeController.php',
    'StatusController.php',
    'WebhookController.php',
    'MetricController.php',
    'DlqController.php',
    'InternalPaymentProvider.php',
    'StripePaymentProvider.php',
    'PayPalPaymentProvider.php',
    'Money.php',
    'RequireScope.php',
    'ScopeGuardSubscriber.php',
];

foreach ($forbiddenLegacyBasenames as $basename) {
    $found = paying_find_basename($root, $basename);
    if ($found !== null) {
        $errors[] = 'Forbidden legacy class file still present: ' . $found;
    }
}

echo "Paying canonical structure closure report\n";
echo 'Required reports: ' . count($requiredReports) . "\n";
echo 'Required composer scripts: ' . count($requiredComposerScripts) . "\n";
echo 'Forbidden legacy basenames checked: ' . count($forbiddenLegacyBasenames) . "\n";
echo 'Expected canonical basenames checked: ' . count($expectedCanonicalBasenames) . "\n";

foreach ($resolvedCanonicalPaths as $basename => $path) {
    echo '[OK] ' . $basename . ' => ' . $path . "\n";
}

if ($warnings !== []) {
    foreach ($warnings as $warning) {
        echo '[WARN] ' . $warning . "\n";
    }
}

if ($errors !== []) {
    echo "Status: FAIL\n";
    foreach ($errors as $error) {
        echo '- ' . $error . "\n";
    }
    exit(1);
}

echo "Status: OK\n";
