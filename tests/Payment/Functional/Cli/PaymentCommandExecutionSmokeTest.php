<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Functional\Cli;

use App\Entity\Payment\Payment;
use App\Infrastructure\Payment\Console\OutboxRunCommand;
use App\Infrastructure\Payment\Console\ProjectionRebuildCommand;
use App\Infrastructure\Payment\Console\ProjectionSyncCommand;
use App\Infrastructure\Payment\Console\ReconcileRunCommand;
use App\Infrastructure\Payment\OutboxWorker;
use App\Service\Payment\ProjectionSyncInterface;
use App\Service\Payment\ReconciliationServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

final class PaymentCommandExecutionSmokeTest extends TestCase
{
    public function testProjectionSyncCommandExecutesAndPrintsSyncedCount(): void
    {
        $sync = $this->createMock(ProjectionSyncInterface::class);
        $sync->expects(self::once())
            ->method('sync')
            ->with(25)
            ->willReturn(7);

        $command = new ProjectionSyncCommand($sync);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['limit' => '25']));
        self::assertStringContainsString('Synced: 7', $tester->getDisplay());
    }

    public function testProjectionRebuildCommandExecutesAndPrintsRebuiltCount(): void
    {
        $sync = $this->createMock(ProjectionSyncInterface::class);
        $sync->expects(self::once())
            ->method('rebuild')
            ->with(40)
            ->willReturn(11);

        $command = new ProjectionRebuildCommand($sync);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['batch' => '40']));
        self::assertStringContainsString('Rebuilt: 11', $tester->getDisplay());
    }

    public function testOutboxRunCommandExecutesWithRetryFailedFlag(): void
    {
        /** @var OutboxWorker&MockObject $worker */
        $worker = $this->getMockBuilder(OutboxWorker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $worker->expects(self::once())
            ->method('run')
            ->with(3, true)
            ->willReturn(2);

        $command = new OutboxRunCommand($worker);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            'limit' => '3',
            '--retry-failed' => true,
        ]));
        self::assertStringContainsString('Published: 2', $tester->getDisplay());
    }

    public function testReconcileRunCommandExecutesForAllReturnedProcessingIds(): void
    {
        $payment = $this->createConfiguredMock(Payment::class, []);
        $first = (string) new Ulid();
        $second = (string) new Ulid();

        $svc = $this->createMock(ReconciliationServiceInterface::class);
        $svc->expects(self::once())
            ->method('listProcessingIds')
            ->with(200)
            ->willReturn([$first, $second]);
        $reconciled = [];
        $svc->expects(self::exactly(2))
            ->method('reconcile')
            ->willReturnCallback(static function (Ulid $id) use ($payment, &$reconciled): Payment {
                $reconciled[] = (string) $id;

                return $payment;
            });

        $command = new ReconcileRunCommand($svc);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Reconciled: 2', $tester->getDisplay());
        self::assertCount(2, $reconciled);
        self::assertSame([$first, $second], $reconciled);
    }
}
