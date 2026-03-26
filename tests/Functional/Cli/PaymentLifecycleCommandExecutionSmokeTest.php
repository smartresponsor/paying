<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use App\Entity\Payment;
use App\Infrastructure\Console\PaymentLifecycleCommand;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

final class PaymentLifecycleCommandExecutionSmokeTest extends TestCase
{
    public function testCreateActionDelegatesToPaymentServiceAndPrintsJson(): void
    {
        $payment = new Payment(new Ulid('01ARZ3NDEKTSV4RRFFQ69G5FAV'), PaymentStatus::new, '50.00', 'USD');

        $paymentService = $this->createMock(PaymentServiceInterface::class);
        $paymentService->expects(self::once())
            ->method('create')
            ->with('order-1001', 5000, 'USD')
            ->willReturn($payment);

        $command = new PaymentLifecycleCommand(
            $paymentService,
            $this->createMock(PaymentStartServiceInterface::class),
            $this->createMock(PaymentRepositoryInterface::class),
            $this->createMock(ProviderGuardInterface::class),
            $this->createMock(RefundServiceInterface::class),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--action' => 'create',
            '--order-id' => 'order-1001',
            '--amount-minor' => '5000',
            '--currency' => 'usd',
        ]));

        $display = trim($tester->getDisplay());
        self::assertJson($display);
        self::assertStringContainsString('"action":"create"', $display);
        self::assertStringContainsString('"status":"new"', $display);
    }

    public function testFinalizeActionSyncsExistingPaymentAndSavesIt(): void
    {
        $paymentId = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
        $existing = new Payment(new Ulid($paymentId), PaymentStatus::processing, '50.00', 'USD');
        $resolved = new Payment(new Ulid($paymentId), PaymentStatus::completed, '50.00', 'USD');
        $resolved->withProviderRef('stripe_pi_123');

        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('find')
            ->with($paymentId)
            ->willReturn($existing);
        $repo->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($existing));

        /** @var ProviderGuardInterface&MockObject $guard */
        $guard = $this->createMock(ProviderGuardInterface::class);
        $guard->expects(self::once())
            ->method('finalize')
            ->with(
                'internal',
                self::callback(static fn (Ulid $id): bool => (string) $id === $paymentId),
                [
                    'providerRef' => 'stripe_pi_123',
                    'gatewayTransactionId' => 'txn_555',
                    'status' => 'completed',
                ],
            )
            ->willReturn($resolved);

        $command = new PaymentLifecycleCommand(
            $this->createMock(PaymentServiceInterface::class),
            $this->createMock(PaymentStartServiceInterface::class),
            $repo,
            $guard,
            $this->createMock(RefundServiceInterface::class),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--action' => 'finalize',
            '--payment-id' => $paymentId,
            '--provider' => 'internal',
            '--provider-ref' => 'stripe_pi_123',
            '--gateway-transaction-id' => 'txn_555',
            '--status' => 'completed',
        ]));

        $display = trim($tester->getDisplay());
        self::assertStringContainsString('"action":"finalize"', $display);
        self::assertStringContainsString('"status":"completed"', $display);
        self::assertStringContainsString('"providerRef":"stripe_pi_123"', $display);
    }
}
