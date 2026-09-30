<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Cli;

use App\Paying\Infrastructure\Console\PaymentDlqReplayCommand;
use App\Paying\Infrastructure\Console\PaymentIdemPurgeCommand;
use App\Paying\Infrastructure\Console\PaymentSlaReportCommand;
use App\Paying\ServiceInterface\DlqServiceInterface;
use App\Paying\ServiceInterface\IdempotencyStoreInterface;
use App\Paying\ServiceInterface\SlaReporterInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Exercises the payment operational command execution smoke scenario within the payment cli test surface.
 */
final class PaymentOperationalCommandExecutionSmokeTest extends TestCase
{
    /**
     * Verifies that dlq replay command replays rows and prints count.
     */
    public function testPaymentDlqReplayCommandReplaysRowsAndPrintsCount(): void
    {
        /** @var DlqServiceInterface&MockObject $dlq */
        $dlq = $this->createMock(DlqServiceInterface::class);
        $dlq->expects(self::once())
            ->method('list')
            ->willReturn([
                ['id' => 11, 'topic' => 'payment.completed', 'payload' => '{"paymentId":"01TEST"}'],
                ['id' => 12, 'topic' => 'payment.failed', 'payload' => '{"paymentId":"01FAIL"}'],
            ]);
        $replayed = [];
        $dlq->expects(self::exactly(2))
            ->method('replay')
            ->willReturnCallback(static function (int $id) use (&$replayed): bool {
                $replayed[] = $id;

                return true;
            });

        $command = new PaymentDlqReplayCommand($dlq);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['limit' => '2']));
        self::assertStringContainsString('Replayed: 2', $tester->getDisplay());
        self::assertSame([11, 12], $replayed);
    }

    /**
     * Verifies that idem purge command prints purged count.
     */
    public function testPaymentIdemPurgeCommandPrintsPurgedCount(): void
    {
        $store = $this->createMock(IdempotencyStoreInterface::class);
        $store->expects(self::once())
            ->method('purgeExpired')
            ->willReturn(4);

        $command = new PaymentIdemPurgeCommand($store);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Purged: 4', $tester->getDisplay());
    }

    /**
     * Verifies that sla report command prints json report for window.
     */
    public function testPaymentSlaReportCommandPrintsJsonReportForWindow(): void
    {
        /** @var SlaReporterInterface&MockObject $reporter */
        $reporter = $this->createMock(SlaReporterInterface::class);

        $reporter->expects(self::once())
            ->method('since')
            ->with('P7D')
            ->willReturn([
                'window' => 'P7D',
                'total' => 10,
                'completed' => 7,
                'failed' => 1,
                'canceled' => 1,
                'refunded' => 1,
                'successRate' => 70.0,
            ]);

        $command = new PaymentSlaReportCommand($reporter);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['window' => 'P7D']));

        $display = trim($tester->getDisplay());
        self::assertJson($display);
        self::assertStringContainsString('"window":"P7D"', $display);
        self::assertStringContainsString('"successRate":70', $display);
    }
}
