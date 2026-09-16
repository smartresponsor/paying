<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\PaymentRetryExecutor;
use App\Paying\ServiceInterface\PaymentMetricInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the retry executor scenario within the payment unit test surface.
 */
final class PaymentRetryExecutorTest extends TestCase
{
    /**
     * Verifies that retries until success.
     */
    public function testRetriesUntilSuccess(): void
    {
        $calls = 0;

        /** @var PaymentMetricInterface&MockObject $metric */
        $metric = $this->createMock(PaymentMetricInterface::class);
        $metric->expects(self::exactly(2))->method('incRetryAttempt');

        $executor = new PaymentRetryExecutor($metric, 3, 0, 1.0, 0, 0);

        $result = $executor->execute(function () use (&$calls) {
            ++$calls;
            if ($calls < 3) {
                throw new \RuntimeException('fail');
            }

            return 'ok';
        });

        self::assertSame('ok', $result);
    }

    /**
     * Verifies that exhausted throws.
     */
    public function testExhaustedThrows(): void
    {
        /** @var PaymentMetricInterface&MockObject $metric */
        $metric = $this->createMock(PaymentMetricInterface::class);
        $metric->expects(self::once())->method('incRetryExhausted');

        $executor = new PaymentRetryExecutor($metric, 2, 0, 1.0, 0, 0);

        $this->expectException(\RuntimeException::class);

        $executor->execute(fn () => throw new \RuntimeException('fail'));
    }
}
