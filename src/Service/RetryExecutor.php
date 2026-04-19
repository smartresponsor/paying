<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\MetricInterface;
use App\ServiceInterface\RetryExecutorInterface;
use Random\RandomException;

/**
 * Provides the retry executor service used by the payment lifecycle and operator-facing flows.
 */
readonly class RetryExecutor implements RetryExecutorInterface
{
    public function __construct(
        private MetricInterface $metric,
        private int $maxAttempts = 3,
        private int $baseMs = 50,
        private float $multiplier = 2.0,
        private int $maxSleepMs = 1000,
        private int $jitterMs = 0,
    ) {
    }

    /**
     * Provides the execute behavior for the retry executor component.
     */
    public function execute(callable $callable): mixed
    {
        $attempt = 1;
        $sleep = $this->baseMs;

        while (true) {
            try {
                return $callable();
            } catch (RandomException $e) {
            } catch (RandomException $e) {
            }
        }
    }
}
