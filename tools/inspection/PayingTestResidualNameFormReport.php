<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$warnings = [];

$coveredLegacyTests = [
    'tests/Unit/ApiErrorResponseFactoryTest.php' => 'tests/Unit/PaymentApiErrorResponseFactoryTest.php',
    'tests/Unit/ApiJsonBodyDecoderTest.php' => 'tests/Unit/PaymentApiJsonBodyDecoderTest.php',
    'tests/Unit/ApiRequestValidatorTest.php' => 'tests/Unit/PaymentApiRequestValidatorTest.php',
    'tests/Unit/FinalizeControllerTest.php' => 'tests/Unit/PaymentFinalizeControllerTest.php',
    'tests/Unit/MoneyTest.php' => 'tests/Unit/ValueObject/PaymentMoneyTest.php',
    'tests/Unit/OutboxPublisherEnqueueTest.php' => null,
    'tests/Unit/OutboxWorkerRetryTest.php' => null,
    'tests/Unit/PayPalEventNormalizerTest.php' => null,
    'tests/Unit/ProjectionLagServiceTest.php' => 'tests/Unit/PaymentProjectionLagServiceTest.php',
    'tests/Unit/ProviderGuardTest.php' => 'tests/Unit/PaymentProviderGuardTest.php',
    'tests/Unit/RefundServiceTest.php' => 'tests/Unit/PaymentRefundServiceTest.php',
    'tests/Unit/ResponseHeaderSubscriberTest.php' => 'tests/Unit/PaymentResponseHeaderSubscriberTest.php',
    'tests/Unit/RetryExecutorTest.php' => 'tests/Unit/PaymentRetryExecutorTest.php',
    'tests/Unit/ScopeGuardSubscriberTest.php' => 'tests/Unit/PaymentScopeGuardSubscriberTest.php',
    'tests/Unit/StripeEventNormalizerTest.php' => null,
    'tests/Unit/TokenVerifierTest.php' => 'tests/Unit/PaymentTokenVerifierTest.php',
    'tests/Unit/ValidationErrorMapperTest.php' => 'tests/Unit/PaymentValidationErrorMapperTest.php',
    'tests/Unit/ValueObject/MoneyTest.php' => 'tests/Unit/ValueObject/PaymentMoneyTest.php',
];

foreach ($coveredLegacyTests as $legacyPath => $replacementPath) {
    $legacyExists = is_file($root . DIRECTORY_SEPARATOR . $legacyPath);
    $replacementExists = $replacementPath !== null && is_file($root . DIRECTORY_SEPARATOR . $replacementPath);

    if ($legacyExists && $replacementExists) {
        $errors[] = sprintf('Legacy duplicate test remains beside canonical replacement: %s -> %s', $legacyPath, $replacementPath);
        continue;
    }

    if ($legacyExists && $replacementPath === null) {
        $warnings[] = 'Legacy test remains and needs canonical mapping before retirement: ' . $legacyPath;
        continue;
    }

    if (!$legacyExists && $replacementPath !== null && !$replacementExists) {
        $warnings[] = 'Neither legacy nor canonical test exists for mapped pair: ' . $legacyPath . ' -> ' . $replacementPath;
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

        if (!preg_match('/\b(?:final\s+|readonly\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $matches)) {
            if ($path !== 'tests/bootstrap.php') {
                $warnings[] = 'Unable to detect class-like symbol in test file: ' . $path;
            }
            continue;
        }

        $className = $matches[1];

        if (str_starts_with($className, 'PaymentPayment')) {
            $errors[] = sprintf('Double-prefix test drift: %s declares %s', $path, $className);
        }

        if (str_ends_with($className, 'Test') && !str_starts_with($className, 'Payment')) {
            $warnings[] = sprintf('Non-prefixed test class remains for review: %s declares %s', $path, $className);
        }
    }
}

fwrite(STDOUT, "Paying test residual name-form report\n");
fwrite(STDOUT, str_repeat('=', 38) . "\n");
fwrite(STDOUT, 'Mapped legacy tests checked: ' . count($coveredLegacyTests) . "\n");
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
