<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\RetryExecutor;
use App\ServiceInterface\MetricInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RetryExecutorTest extends TestCase
{
    public function testRetriesUntilSuccess(): void
    {
        $calls = 0;

        /** @var MetricInterface&MockObject $metric */
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects(self::exactly(2))->method('incRetryAttempt');

        $executor = new RetryExecutor($metric, 3, 0, 1.0, 0, 0);

        $result = $executor->execute(function () use (&$calls) {
            ++$calls;
            if ($calls < 3) {
                throw new \RuntimeException('fail');
            }

            return 'ok';
        });

        self::assertSame('ok', $result);
    }

    public function testExhaustedThrows(): void
    {
        /** @var MetricInterface&MockObject $metric */
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects(self::once())->method('incRetryExhausted');

        $executor = new RetryExecutor($metric, 2, 0, 1.0, 0, 0);

        $this->expectException(\RuntimeException::class);

        $executor->execute(fn () => throw new \RuntimeException('fail'));
    }
}
