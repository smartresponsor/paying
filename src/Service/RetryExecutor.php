<?php

declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\ServiceInterface\MetricInterface;
use App\Paying\ServiceInterface\RetryExecutorInterface;
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
        $sleepMs = max(0, $this->baseMs);

        while (true) {
            try {
                return $callable();
            } catch (\Throwable $e) {
                if ($attempt >= $this->maxAttempts) {
                    $this->metric->incRetryExhausted();

                    throw $e;
                }

                $this->metric->incRetryAttempt();

                $delayMs = $sleepMs;

                if ($this->jitterMs > 0) {
                    try {
                        $delayMs += random_int(0, $this->jitterMs);
                    } catch (RandomException) {
                        // Keep deterministic fallback delay when entropy is unavailable.
                    }
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }

                ++$attempt;

                $nextSleepMs = (int) ceil($sleepMs * $this->multiplier);
                $sleepMs = $this->maxSleepMs > 0
                    ? min($this->maxSleepMs, max(0, $nextSleepMs))
                    : max(0, $nextSleepMs);
            }
        }
    }
}
