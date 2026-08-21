<?php

declare(strict_types=1);

/**
 * Closes the Paying test nameEntity-form canonization contour introduced across Waves 23-26.
 */
final class PayingTestCanonicalClosureReport
{
    /** @var array<string, string> */
    private const RETIRED_MAPPED_TESTS = [
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

    /** @var array<string, string> */
    private const CANONICALIZED_UNMAPPED_TESTS = [
        'tests/Unit/OutboxPublisherEnqueueTest.php' => 'tests/Unit/PaymentOutboxPublisherEnqueueTest.php',
        'tests/Unit/OutboxWorkerRetryTest.php' => 'tests/Unit/PaymentOutboxWorkerRetryTest.php',
        'tests/Unit/PayPalEventNormalizerTest.php' => 'tests/Unit/PaymentPayPalEventNormalizerTest.php',
        'tests/Unit/StripeEventNormalizerTest.php' => 'tests/Unit/PaymentStripeEventNormalizerTest.php',
    ];

    /** @var list<string> */
    private const REQUIRED_REPORTS = [
        'tools/inspection/PayingTestResidualNameFormReport.php',
        'tools/inspection/PayingTestLegacyDuplicateRetirementReport.php',
        'tools/inspection/PayingTestUnmappedResidualNameFormReport.php',
        'tools/inspection/PayingTestUnmappedCanonicalizationReport.php',
    ];

    /** @var list<string> */
    private const REQUIRED_COMPOSER_SCRIPTS = [
        'report:test-residual-nameEntity-form',
        'report:test-legacy-duplicate-retirement',
        'report:test-unmapped-residual-nameEntity-form',
        'report:test-unmapped-canonicalization',
    ];

    public static function main(string $projectRoot): int
    {
        $root = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $errors = [];
        $warnings = [];

        foreach (self::REQUIRED_REPORTS as $reportPath) {
            if (!is_file($root . '/' . $reportPath)) {
                $errors[] = 'Missing prior test contour report: ' . $reportPath;
            }
        }

        $composer = self::readComposer($root . '/composer.json', $errors);
        $scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
        foreach (self::REQUIRED_COMPOSER_SCRIPTS as $scriptName) {
            if (!array_key_exists($scriptName, $scripts)) {
                $errors[] = 'Missing prior test contour composer script: ' . $scriptName;
            }
        }

        foreach (self::allLegacyToCanonicalTests() as $legacyPath => $canonicalPath) {
            if (is_file($root . '/' . $legacyPath)) {
                $errors[] = 'Legacy test remains after canonical test contour closure: ' . $legacyPath;
            }
            if (!is_file($root . '/' . $canonicalPath)) {
                $errors[] = 'Canonical test is missing for retired legacy test: ' . $canonicalPath;
            }
        }

        $testsDirectory = $root . '/tests';
        if (!is_dir($testsDirectory)) {
            $errors[] = 'Missing tests directory.';
        } else {
            self::scanTests($testsDirectory, $root, $errors, $warnings);
        }

        echo "Paying test canonical closure report\n";
        echo "====================================\n";
        echo 'Project root: ' . $root . "\n";
        echo 'Prior test reports checked: ' . count(self::REQUIRED_REPORTS) . "\n";
        echo 'Prior test composer scripts checked: ' . count(self::REQUIRED_COMPOSER_SCRIPTS) . "\n";
        echo 'Legacy-to-canonical test pairs checked: ' . count(self::allLegacyToCanonicalTests()) . "\n";
        echo 'Warnings: ' . count($warnings) . "\n";
        foreach ($warnings as $warning) {
            echo '[WARN] ' . $warning . "\n";
        }

        if ($errors !== []) {
            echo 'Errors: ' . count($errors) . "\n";
            foreach ($errors as $error) {
                echo '[ERROR] ' . $error . "\n";
            }
            echo "Status: FAIL\n";
            return 1;
        }

        echo "Errors: 0\n";
        echo "Status: OK\n";
        return 0;
    }

    /** @return array<string, string> */
    private static function allLegacyToCanonicalTests(): array
    {
        return self::RETIRED_MAPPED_TESTS + self::CANONICALIZED_UNMAPPED_TESTS;
    }

    /** @param list<string> $errors */
    private static function readComposer(string $composerPath, array &$errors): array
    {
        if (!is_file($composerPath)) {
            $errors[] = 'Missing composer.json.';
            return [];
        }
        $contents = file_get_contents($composerPath);
        if ($contents === false) {
            $errors[] = 'Unable to read composer.json.';
            return [];
        }
        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            $errors[] = 'composer.json is not valid JSON.';
            return [];
        }
        return $decoded;
    }

    /** @param list<string> $errors @param list<string> $warnings */
    private static function scanTests(string $testsDirectory, string $root, array &$errors, array &$warnings): void
    {
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
            if (str_contains($contents, 'PaymentPayment')) {
                $errors[] = 'Double Payment prefix drift in test file: ' . $path;
            }
            if (preg_match('/\b(?:final\s+|readonly\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $matches) !== 1) {
                if ($path !== 'tests/bootstrap.php') {
                    $warnings[] = 'Unable to detect class-like symbol in test file: ' . $path;
                }
                continue;
            }
            $className = $matches[1];
            $fileStem = pathinfo($path, PATHINFO_FILENAME);
            if ($className !== $fileStem && str_ends_with($className, 'Test')) {
                $warnings[] = sprintf('Test class/file stem mismatch: %s declares %s', $path, $className);
            }
        }
    }
}

exit(PayingTestCanonicalClosureReport::main(getcwd()));
