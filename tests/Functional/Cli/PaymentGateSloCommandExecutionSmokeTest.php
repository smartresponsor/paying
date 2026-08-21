<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Cli;

use App\Paying\Infrastructure\Console\PaymentGateSloCommand;
use App\Paying\Service\PaymentMetric;
use App\Paying\ServiceInterface\PaymentProjectionLagServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Exercises the payment gate slo command execution smoke scenario within the payment cli test surface.
 */
final class PaymentGateSloCommandExecutionSmokeTest extends TestCase
{
    /**
     * Verifies that gate slo command prints metrics and succeeds when there are no failures.
     */
    public function testPaymentGateSloCommandPrintsMetricsAndSucceedsWhenThereAreNoFailures(): void
    {
        /** @var PaymentMetric&MockObject $metric */
        $metric = $this->getMockBuilder(PaymentMetric::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['export'])
            ->getMock();

        $metric->expects(self::once())
            ->method('export')
            ->willReturn("payment_total 10\npayment_failure_total 0\n");

        /** @var PaymentProjectionLagServiceInterface&MockObject $projectionLag */
        $projectionLag = $this->createMock(PaymentProjectionLagServiceInterface::class);
        $projectionLag->expects(self::once())
            ->method('snapshot')
            ->willReturn([
                'updatedAtData' => '2025-11-07 10:00:00',
                'updatedAtInfra' => '2025-11-07 10:00:00',
                'projectionLagMs' => 0,
            ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $command = new PaymentGateSloCommand($metric, $projectionLag, $logger);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('payment_total 10', $tester->getDisplay());
        self::assertStringContainsString('payment_failure_total 0', $tester->getDisplay());
    }

    /**
     * Verifies that gate slo command fails when metric reports failures.
     */
    public function testPaymentGateSloCommandFailsWhenMetricReportsFailures(): void
    {
        /** @var PaymentMetric&MockObject $metric */
        $metric = $this->getMockBuilder(PaymentMetric::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['export'])
            ->getMock();

        $metric->expects(self::once())
            ->method('export')
            ->willReturn("payment_total 10\npayment_failure_total 2\n");

        /** @var PaymentProjectionLagServiceInterface&MockObject $projectionLag */
        $projectionLag = $this->createMock(PaymentProjectionLagServiceInterface::class);
        $projectionLag->expects(self::once())
            ->method('snapshot')
            ->willReturn([
                'updatedAtData' => '2025-11-07 10:00:00',
                'updatedAtInfra' => '2025-11-07 10:00:00',
                'projectionLagMs' => 0,
            ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $command = new PaymentGateSloCommand($metric, $projectionLag, $logger);
        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('payment_failure_total 2', $tester->getDisplay());
    }
}
