<?php

declare(strict_types=1);

/**
 * Verifies the canonicalized unmapped test residual set introduced by Wave 26.
 *
 * This report is intentionally narrow: it checks only the four unit tests that were left
 * unmapped by the previous mapped duplicate retirement wave.
 */
final class PayingTestUnmappedCanonicalizationReport
{
    /** @var array<string, string> */
    private const CANONICAL_TESTS = [
        'tests/Unit/OutboxPublisherEnqueueTest.php' => 'tests/Unit/PaymentOutboxPublisherEnqueueTest.php',
        'tests/Unit/OutboxWorkerRetryTest.php' => 'tests/Unit/PaymentOutboxWorkerRetryTest.php',
        'tests/Unit/PayPalEventNormalizerTest.php' => 'tests/Unit/PaymentPayPalEventNormalizerTest.php',
        'tests/Unit/StripeEventNormalizerTest.php' => 'tests/Unit/PaymentStripeEventNormalizerTest.php',
    ];

    /** @var array<string, string> */
    private const EXPECTED_CLASSES = [
        'tests/Unit/PaymentOutboxPublisherEnqueueTest.php' => 'PaymentOutboxPublisherEnqueueTest',
        'tests/Unit/PaymentOutboxWorkerRetryTest.php' => 'PaymentOutboxWorkerRetryTest',
        'tests/Unit/PaymentPayPalEventNormalizerTest.php' => 'PaymentPayPalEventNormalizerTest',
        'tests/Unit/PaymentStripeEventNormalizerTest.php' => 'PaymentStripeEventNormalizerTest',
    ];

    public static function main(string $projectRoot): int
    {
        $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $errors = [];

        foreach (self::CANONICAL_TESTS as $legacyPath => $canonicalPath) {
            if (is_file($projectRoot . '/' . $legacyPath)) {
                $errors[] = 'Legacy unmapped test still exists after Wave 26: ' . $legacyPath;
            }

            if (!is_file($projectRoot . '/' . $canonicalPath)) {
                $errors[] = 'Canonical Wave 26 test is missing: ' . $canonicalPath;
            }
        }

        foreach (self::EXPECTED_CLASSES as $path => $className) {
            $absolute = $projectRoot . '/' . $path;
            if (!is_file($absolute)) {
                continue;
            }

            $contents = file_get_contents($absolute);
            if ($contents === false) {
                $errors[] = 'Unable to read canonical Wave 26 test: ' . $path;
                continue;
            }

            if (!preg_match('/\bfinal\s+class\s+' . preg_quote($className, '/') . '\b/', $contents)) {
                $errors[] = sprintf('Canonical Wave 26 test %s does not declare %s.', $path, $className);
            }

            if (str_contains($contents, 'PaymentPayment')) {
                $errors[] = 'Double Payment prefix drift in Wave 26 test: ' . $path;
            }
        }

        echo "Paying test unmapped canonicalization report\n";
        echo "============================================\n";
        echo 'Project root: ' . $projectRoot . "\n";
        echo 'Canonicalized tests checked: ' . count(self::CANONICAL_TESTS) . "\n";
        echo 'Errors: ' . count($errors) . "\n";

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
}

exit(PayingTestUnmappedCanonicalizationReport::main(getcwd()));
