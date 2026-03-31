<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use App\Infrastructure\Console\PaymentLifecycleCommand;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartResult;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PaymentLifecycleBatchRestartTest extends TestCase
{
    public function testRestartFailedProcessesAllIdsAndPrintsSummary(): void
    {
        $ids = ['id1', 'id2', 'id3'];

        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('listIdsByStatuses')
            ->willReturn($ids);

        /** @var PaymentStartServiceInterface&MockObject $start */
        $start = $this->createMock(PaymentStartServiceInterface::class);
        $start->expects(self::exactly(3))
            ->method('restart')
            ->willReturnCallback(fn () => new PaymentStartResult($this->createStub(\App\Entity\Payment::class), null, []));

        $command = new PaymentLifecycleCommand(
            $this->createMock(PaymentServiceInterface::class),
            $start,
            $repo,
            $this->createMock(ProviderGuardInterface::class),
            $this->createMock(RefundServiceInterface::class)
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--action' => 'restart-failed',
            '--provider' => 'stripe',
            '--limit' => '3',
        ]));

        $output = $tester->getDisplay();

        self::assertStringContainsString('restart-failed', $output);
        self::assertStringContainsString('"processed":3', $output);
        self::assertStringContainsString('"restarted":3', $output);
    }
}
