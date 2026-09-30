<?php

declare(strict_types=1);

/**
 * Reports unmapped legacy test nameEntity-form leftovers after the mapped test duplicate retirement wave.
 *
 * This inspection is intentionally report-only. It does not delete files and it does not decide
 * whether a legacy test is obsolete. It creates a narrow backlog for the next safe mapping wave.
 */
final class PayingTestUnmappedResidualNameFormReport
{
    /** @var list<string> */
    private const LEGACY_TEST_BASENAMES = [
        'ApiJsonBodyDecoderTest.php',
        'CircuitBreakerTest.php',
        'DbalIdempotencyStoreTest.php',
        'GatewayCodeTest.php',
        'IdempotencyServiceTest.php',
        'JsonSchemaValidatorTest.php',
        'MoneyTest.php',
        'OutboxPublisherTest.php',
        'OutboxWorkerTest.php',
        'PayPalGatewayTest.php',
        'PayPalSignatureValidatorTest.php',
        'ProviderRouterTest.php',
        'RedisIdempotencyStoreTest.php',
        'RetryExecutorTest.php',
        'ScopeGuardSubscriberTest.php',
        'StripeGatewayTest.php',
        'StripeSignatureValidatorTest.php',
        'TokenVerifierTest.php',
        'ValidationErrorMapperTest.php',
        'WebhookVerifierTest.php',
    ];

    public static function main(string $projectRoot): int
    {
        $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $testsRoot = $projectRoot . '/tests';
        $errors = [];
        $warnings = [];

        if (!is_dir($testsRoot)) {
            $errors[] = 'Missing tests directory.';
        } else {
            foreach (self::collectPhpFiles($testsRoot) as $file) {
                $basename = basename($file);
                $relative = self::relativePath($projectRoot, $file);

                if (str_contains($basename, 'PaymentPayment')) {
                    $errors[] = 'Double Payment prefix in test file: ' . $relative;
                    continue;
                }

                if (in_array($basename, self::LEGACY_TEST_BASENAMES, true)) {
                    $canonical = self::canonicalCandidate($basename);
                    $canonicalExists = self::findBasename($testsRoot, $canonical);

                    if ($canonicalExists !== null) {
                        $warnings[] = sprintf(
                            'Mapped legacy test still present: %s; canonical candidate exists at %s',
                            $relative,
                            self::relativePath($projectRoot, $canonicalExists)
                        );
                    } else {
                        $warnings[] = sprintf(
                            'Unmapped legacy test needs explicit next-wave decision: %s; suggested candidate basename %s',
                            $relative,
                            $canonical
                        );
                    }
                }
            }
        }

        echo "Paying test unmapped residual nameEntity-form report\n";
        echo "================================================\n";
        echo 'Project root: ' . $projectRoot . "\n";
        echo 'Warnings: ' . count($warnings) . "\n";
        echo 'Errors: ' . count($errors) . "\n";

        foreach ($warnings as $warning) {
            echo '[WARN] ' . $warning . "\n";
        }

        foreach ($errors as $error) {
            echo '[ERROR] ' . $error . "\n";
        }

        if ($errors !== []) {
            echo "Status: FAIL\n";
            return 1;
        }

        echo "Status: OK\n";
        return 0;
    }

    /** @return list<string> */
    private static function collectPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $files[] = str_replace('\\', '/', $file->getPathname());
        }

        sort($files);

        return $files;
    }

    private static function canonicalCandidate(string $basename): string
    {
        if (str_starts_with($basename, 'Payment')) {
            return $basename;
        }

        if ($basename === 'ScopeGuardSubscriberTest.php') {
            return 'PaymentScopeGuardSubscriberTest.php';
        }

        return 'Payment' . $basename;
    }

    private static function findBasename(string $directory, string $basename): ?string
    {
        foreach (self::collectPhpFiles($directory) as $file) {
            if (basename($file) === $basename) {
                return $file;
            }
        }

        return null;
    }

    private static function relativePath(string $root, string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');

        if (str_starts_with($path, $root . '/')) {
            return substr($path, strlen($root) + 1);
        }

        return $path;
    }
}

exit(PayingTestUnmappedResidualNameFormReport::main(getcwd()));
