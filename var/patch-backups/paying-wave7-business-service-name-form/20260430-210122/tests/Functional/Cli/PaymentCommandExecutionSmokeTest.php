<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Cli;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Infrastructure\Console\PaymentOutboxRunCommand;
use App\Paying\Infrastructure\Console\PaymentProjectionRebuildCommand;
use App\Paying\Infrastructure\Console\PaymentProjectionSyncCommand;
use App\Paying\Infrastructure\Console\PaymentReconcileRunCommand;
use App\Paying\Infrastructure\PaymentOutboxWorker;
use App\Paying\ServiceInterface\ProjectionSyncInterface;
use App\Paying\ServiceInterface\ReconciliationServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

/**
 * Exercises the payment command execution smoke scenario within the payment cli test surface.
 */
final class PaymentCommandExecutionSmokeTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that projection sync command executes and prints synced count.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testPaymentProjectionSyncCommandExecutesAndPrintsSyncedCount(): void
    {
        $sync = $this->createMock(ProjectionSyncInterface::class);
        $sync->expects(self::once())
            ->method('sync')
            ->with(25)
            ->willReturn(7);

        $command = new PaymentProjectionSyncCommand($sync);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['limit' => '25']));
        self::assertStringContainsString('Synced: 7', $tester->getDisplay());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that projection rebuild command executes and prints rebuilt count.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testPaymentProjectionRebuildCommandExecutesAndPrintsRebuiltCount(): void
    {
        $sync = $this->createMock(ProjectionSyncInterface::class);
        $sync->expects(self::once())
            ->method('rebuild')
            ->with(40)
            ->willReturn(11);

        $command = new PaymentProjectionRebuildCommand($sync);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute(['batch' => '40']));
        self::assertStringContainsString('Rebuilt: 11', $tester->getDisplay());
    }

    /**
     * Verifies that outbox run command executes with retry failed flag.
     */
    public function testPaymentOutboxRunCommandExecutesWithRetryFailedFlag(): void
    {
        /** @var PaymentOutboxWorker&MockObject $worker */
        $worker = $this->getMockBuilder(PaymentOutboxWorker::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $worker->expects(self::once())
            ->method('run')
            ->with(3, true)
            ->willReturn(2);

        $command = new PaymentOutboxRunCommand($worker);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            'limit' => '3',
            '--retry-failed' => true,
        ]));
        self::assertStringContainsString('Published: 2', $tester->getDisplay());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    /**
     * Verifies that reconcile run command executes for all returned processing ids.
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testPaymentReconcileRunCommandExecutesForAllReturnedProcessingIds(): void
    {
        $payment = $this->createConfiguredMock(PaymentEntity::class, []);
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
            ->willReturnCallback(static function (Ulid $id) use ($payment, &$reconciled): PaymentEntity {
                $reconciled[] = (string) $id;

                return $payment;
            });

        $command = new PaymentReconcileRunCommand($svc);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Reconciled: 2', $tester->getDisplay());
        self::assertCount(2, $reconciled);
        self::assertSame([$first, $second], $reconciled);
    }
}
