<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Functional\Cli;

use App\Infrastructure\Payment\Console\DlqReplayCommand;
use App\Infrastructure\Payment\Console\IdemPurgeCommand;
use App\Infrastructure\Payment\Console\SlaReportCommand;
use App\Infrastructure\Payment\OutboxPublisherInterface;
use App\Service\Payment\IdempotencyStoreInterface;
use App\Service\Payment\SlaReporter;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PaymentOperationalCommandExecutionSmokeTest extends TestCase
{
    public function testDlqReplayCommandReplaysRowsAndPrintsCount(): void
    {
        /** @var Connection&MockObject $data */
        $data = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAllAssociative', 'executeStatement'])
            ->getMock();

        $data->expects(self::once())
            ->method('fetchAllAssociative')
            ->with(self::stringContains('SELECT * FROM payment_dlq'))
            ->willReturn([
                ['id' => 11, 'topic' => 'payment.completed', 'payload' => '{"paymentId":"01TEST"}'],
                ['id' => 12, 'topic' => 'payment.failed', 'payload' => '{"paymentId":"01FAIL"}'],
            ]);

        $data->expects(self::exactly(2))
            ->method('executeStatement')
            ->with(
                self::stringContains('DELETE FROM payment_dlq'),
                self::callback(static fn (array $params): bool => isset($params['id']) && in_array($params['id'], [11, 12], true)),
                self::anything(),
            )
            ->willReturn(1);

        $publisher = $this->createMock(OutboxPublisherInterface::class);
        $enqueued = [];
        $publisher->expects(self::exactly(2))
            ->method('enqueue')
            ->willReturnCallback(static function (string $topic, array $payload) use (&$enqueued): void {
                $enqueued[] = [$topic, $payload];
            });

        $command = new DlqReplayCommand($data, $publisher);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['limit' => '2']));
        self::assertStringContainsString('Replayed: 2', $tester->getDisplay());
        self::assertSame([
            ['payment.completed', ['paymentId' => '01TEST']],
            ['payment.failed', ['paymentId' => '01FAIL']],
        ], $enqueued);
    }

    public function testIdemPurgeCommandPrintsPurgedCount(): void
    {
        $store = $this->createMock(IdempotencyStoreInterface::class);
        $store->expects(self::once())
            ->method('purgeExpired')
            ->willReturn(4);

        $command = new IdemPurgeCommand($store);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Purged: 4', $tester->getDisplay());
    }

    public function testSlaReportCommandPrintsJsonReportForWindow(): void
    {
        /** @var SlaReporter&MockObject $reporter */
        $reporter = $this->getMockBuilder(SlaReporter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['since'])
            ->getMock();

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

        $command = new SlaReportCommand($reporter);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['window' => 'P7D']));

        $display = trim($tester->getDisplay());
        self::assertJson($display);
        self::assertStringContainsString('"window":"P7D"', $display);
        self::assertStringContainsString('"successRate":70', $display);
    }
}
