<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use App\Infrastructure\Console\GateSloCommand;
use App\Service\Metric;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PaymentGateSloCommandExecutionSmokeTest extends TestCase
{
    public function testGateSloCommandPrintsMetricsAndSucceedsWhenThereAreNoFailures(): void
    {
        /** @var Metric&MockObject $metric */
        $metric = $this->getMockBuilder(Metric::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['export'])
            ->getMock();

        $metric->expects(self::once())
            ->method('export')
            ->willReturn("payment_total 10\npayment_failure_total 0\n");

        /** @var Connection&MockObject $data */
        $data = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();

        $data->expects(self::once())
            ->method('fetchOne')
            ->with(self::stringContains('SELECT MAX(updated_at) FROM payment'))
            ->willReturn('2025-11-07 10:00:00');

        try {
            $logger = $this->createMock(LoggerInterface::class);
        } catch (Exception $e) {
        }
        $logger->expects(self::never())->method('warning');

        $command = new GateSloCommand($metric, $data, $logger);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('payment_total 10', $tester->getDisplay());
        self::assertStringContainsString('payment_failure_total 0', $tester->getDisplay());
    }

    public function testGateSloCommandFailsWhenMetricReportsFailures(): void
    {
        /** @var Metric&MockObject $metric */
        $metric = $this->getMockBuilder(Metric::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['export'])
            ->getMock();

        $metric->expects(self::once())
            ->method('export')
            ->willReturn("payment_total 10\npayment_failure_total 2\n");

        /** @var Connection&MockObject $data */
        $data = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();

        $data->expects(self::once())
            ->method('fetchOne')
            ->willReturn('2025-11-07 10:00:00');

        try {
            $logger = $this->createMock(LoggerInterface::class);
        } catch (Exception $e) {
        }
        $logger->expects(self::never())->method('warning');

        $command = new GateSloCommand($metric, $data, $logger);
        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('payment_failure_total 2', $tester->getDisplay());
    }
}
