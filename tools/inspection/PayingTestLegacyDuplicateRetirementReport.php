<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$warnings = [];

$retiredLegacyTests = [
    'tests/Unit/ApiErrorResponseFactoryTest.php' => 'tests/Unit/PaymentApiErrorResponseFactoryTest.php',
    'tests/Unit/ApiJsonBodyDecoderTest.php' => 'tests/Unit/PaymentApiJsonBodyDecoderTest.php',
    'tests/Unit/ApiRequestValidatorTest.php' => 'tests/Unit/PaymentApiRequestValidatorTest.php',
    'tests/Unit/FinalizeControllerTest.php' => 'tests/Unit/PaymentFinalizeControllerTest.php',
    'tests/Unit/ProjectionLagServiceTest.php' => 'tests/Unit/PaymentProjectionLagServiceTest.php',
    'tests/Unit/ProviderGuardTest.php' => 'tests/Unit/PaymentProviderGuardTest.php',
    'tests/Unit/RefundServiceTest.php' => 'tests/Unit/PaymentRefundServiceTest.php',
    'tests/Unit/ResponseHeaderSubscriberTest.php' => 'tests/Unit/PaymentResponseHeaderSubscriberTest.php',
    'tests/Unit/RetryExecutorTest.php' => 'tests/Unit/PaymentRetryExecutorTest.php',
    'tests/Unit/ScopeGuardSubscriberTest.php' => 'tests/Unit/PaymentScopeGuardSubscriberTest.php',
    'tests/Unit/TokenVerifierTest.php' => 'tests/Unit/PaymentTokenVerifierTest.php',
    'tests/Unit/ValidationErrorMapperTest.php' => 'tests/Unit/PaymentValidationErrorMapperTest.php',
    'tests/Unit/ValueObject/MoneyTest.php' => 'tests/Unit/ValueObject/PaymentMoneyTest.php',
];

foreach ($retiredLegacyTests as $legacyPath => $canonicalPath) {
    if (is_file($root . DIRECTORY_SEPARATOR . $legacyPath)) {
        $errors[] = 'Retired legacy test still exists: ' . $legacyPath;
    }

    if (!is_file($root . DIRECTORY_SEPARATOR . $canonicalPath)) {
        $errors[] = sprintf('Canonical replacement test is missing for %s: %s', $legacyPath, $canonicalPath);
    }
}

$knownUnmappedLegacyTests = [
    'tests/Unit/OutboxPublisherEnqueueTest.php',
    'tests/Unit/OutboxWorkerRetryTest.php',
    'tests/Unit/PayPalEventNormalizerTest.php',
    'tests/Unit/StripeEventNormalizerTest.php',
];

foreach ($knownUnmappedLegacyTests as $path) {
    if (is_file($root . DIRECTORY_SEPARATOR . $path)) {
        $warnings[] = 'Unmapped legacy test remains for a later canonical mapping wave: ' . $path;
    }
}

$testsDirectory = $root . DIRECTORY_SEPARATOR . 'tests';
if (!is_dir($testsDirectory)) {
    $errors[] = 'Missing tests directory.';
} else {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDirectory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            $errors[] = 'Unable to read test file: ' . $path;
            continue;
        }

        if (preg_match('/\b(?:final\s+|readonly\s+|abstract\s+)?(?:class|interface|trait|enum)\s+(PaymentPayment[A-Za-z0-9_]*)/', $contents, $matches)) {
            $errors[] = sprintf('Double-prefix test drift: %s declares %s', $path, $matches[1]);
        }
    }
}

fwrite(STDOUT, "Paying test legacy duplicate retirement report\n");
fwrite(STDOUT, str_repeat('=', 48) . "\n");
fwrite(STDOUT, 'Retired legacy tests checked: ' . count($retiredLegacyTests) . "\n");
fwrite(STDOUT, 'Known unmapped legacy tests: ' . count($knownUnmappedLegacyTests) . "\n");
fwrite(STDOUT, 'Warnings: ' . count($warnings) . "\n");
foreach ($warnings as $warning) {
    fwrite(STDOUT, '[WARN] ' . $warning . "\n");
}

if ($errors !== []) {
    fwrite(STDERR, 'Status: FAIL' . "\n");
    foreach ($errors as $error) {
        fwrite(STDERR, '[ERROR] ' . $error . "\n");
    }
    exit(1);
}

fwrite(STDOUT, 'Status: OK' . "\n");
