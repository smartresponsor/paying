<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use App\Infrastructure\Console\PaymentFailoverRestartCommand;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartResult;
use App\ServiceInterface\PaymentStartServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PaymentFailoverRestartCommandTest extends TestCase
{
    public function testFailoverRestartFallsBackAndPrintsSummary(): void
    {
        $ids = ['payment-1', 'payment-2', 'payment-3'];

        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('listIdsByStatuses')
            ->with(['failed'], 3)
            ->willReturn($ids);

        /** @var PaymentStartServiceInterface&MockObject $start */
        $start = $this->createMock(PaymentStartServiceInterface::class);
        $start->expects(self::exactly(5))
            ->method('restart')
            ->willReturnCallback(function (string $paymentId, string $provider) {
                if ('stripe' === $provider && 'payment-1' === $paymentId) {
                    throw new \RuntimeException('primary down');
                }
                if ('stripe' === $provider && 'payment-2' === $paymentId) {
                    throw new \RuntimeException('primary down');
                }
                if ('internal' === $provider && 'payment-2' === $paymentId) {
                    throw new \RuntimeException('fallback down');
                }

                return new PaymentStartResult($this->createStub(\App\Entity\Payment::class), null, []);
            });

        $command = new PaymentFailoverRestartCommand($repo, $start);
        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([
            '--provider' => 'stripe',
            '--fallback-provider' => 'internal',
            '--limit' => '3',
        ]));

        $display = $tester->getDisplay();
        self::assertStringContainsString('"action":"failover-restart"', $display);
        self::assertStringContainsString('"processed":3', $display);
        self::assertStringContainsString('"restarted":2', $display);
        self::assertStringContainsString('"failed":1', $display);
        self::assertStringContainsString('payment-2', $display);
    }

    public function testFailoverRestartRejectsSameProvider(): void
    {
        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $start = $this->createMock(PaymentStartServiceInterface::class);

        $repo->expects(self::never())->method('listIdsByStatuses');
        $start->expects(self::never())->method('restart');

        $command = new PaymentFailoverRestartCommand($repo, $start);
        $tester = new CommandTester($command);

        self::assertSame(Command::INVALID, $tester->execute([
            '--provider' => 'stripe',
            '--fallback-provider' => 'stripe',
        ]));

        self::assertStringContainsString('distinct --provider and --fallback-provider', $tester->getDisplay());
    }
}
