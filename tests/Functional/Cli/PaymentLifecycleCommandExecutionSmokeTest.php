<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Cli;

use App\Entity\Payment;
use App\Infrastructure\Console\PaymentLifecycleCommand;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\Service\PaymentStartResult;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use App\ValueObject\PaymentStatus;
use PHPUnit\Framework\MockObject\Exception;
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

        try {
            $paymentService = $this->createMock(PaymentServiceInterface::class);
        } catch (Exception $e) {
        }
        $paymentService->expects(self::once())
            ->method('create')
            ->with('order-1001', 5000, 'USD')
            ->willReturn($payment);

        try {
            $command = new PaymentLifecycleCommand(
                $paymentService,
                $this->createMock(PaymentStartServiceInterface::class),
                $this->createMock(PaymentRepositoryInterface::class),
                $this->createMock(ProviderGuardInterface::class),
                $this->createMock(RefundServiceInterface::class),
            );
        } catch (Exception $e) {
        }

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

    public function testStartActionDelegatesToPaymentStartServiceAndPrintsJson(): void
    {
        $payment = new Payment(new Ulid('01ARZ3NDEKTSV4RRFFQ69G5FB0'), PaymentStatus::processing, '50.00', 'USD');
        $started = new PaymentStartResult($payment, 'stripe_pi_123', ['ok' => true]);

        /** @var PaymentStartServiceInterface&MockObject $paymentStartService */
        $paymentStartService = $this->createMock(PaymentStartServiceInterface::class);
        $paymentStartService->expects(self::once())
            ->method('start')
            ->with('stripe', '50.00', 'USD', 'idem-123', 'cli')
            ->willReturn($started);

        $command = new PaymentLifecycleCommand(
            $this->createMock(PaymentServiceInterface::class),
            $paymentStartService,
            $this->createMock(PaymentRepositoryInterface::class),
            $this->createMock(ProviderGuardInterface::class),
            $this->createMock(RefundServiceInterface::class),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--action' => 'start',
            '--provider' => 'stripe',
            '--amount' => '50.00',
            '--currency' => 'usd',
            '--idempotency-key' => 'idem-123',
            '--origin' => 'cli',
        ]));

        $display = trim($tester->getDisplay());
        self::assertJson($display);
        self::assertStringContainsString('"action":"start"', $display);
        self::assertStringContainsString('"status":"processing"', $display);
        self::assertStringContainsString('"providerRef":"stripe_pi_123"', $display);
    }

    public function testFinalizeActionSyncsExistingPaymentAndSavesIt(): void
    {
        $paymentId = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
        $existing = new Payment(new Ulid($paymentId), PaymentStatus::processing, '50.00', 'USD');
        $resolved = new Payment(new Ulid($paymentId), PaymentStatus::completed, '50.00', 'USD');
        $resolved->withProviderRef('stripe_pi_123');

        try {
            $repo = $this->createMock(PaymentRepositoryInterface::class);
        } catch (Exception $e) {
        }
        $repo->expects(self::once())
            ->method('find')
            ->with($paymentId)
            ->willReturn($existing);
        $repo->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($existing));

        /* @var ProviderGuardInterface&MockObject $guard */
        try {
            $guard = $this->createMock(ProviderGuardInterface::class);
        } catch (Exception $e) {
        }
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

        try {
            $command = new PaymentLifecycleCommand(
                $this->createMock(PaymentServiceInterface::class),
                $this->createMock(PaymentStartServiceInterface::class),
                $repo,
                $guard,
                $this->createMock(RefundServiceInterface::class),
            );
        } catch (Exception $e) {
        }

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

    public function testRefundActionDelegatesToRefundServiceAndPrintsJson(): void
    {
        $paymentId = new Ulid('01ARZ3NDEKTSV4RRFFQ69G5FB1');
        $payment = new Payment($paymentId, PaymentStatus::refunded, '50.00', 'USD');
        $payment->withProviderRef('stripe_refund_123');

        /** @var RefundServiceInterface&MockObject $refundService */
        $refundService = $this->createMock(RefundServiceInterface::class);
        $refundService->expects(self::once())
            ->method('refund')
            ->with(self::equalTo($paymentId), '50.00', 'stripe')
            ->willReturn($payment);

        $command = new PaymentLifecycleCommand(
            $this->createMock(PaymentServiceInterface::class),
            $this->createMock(PaymentStartServiceInterface::class),
            $this->createMock(PaymentRepositoryInterface::class),
            $this->createMock(ProviderGuardInterface::class),
            $refundService,
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--action' => 'refund',
            '--payment-id' => (string) $paymentId,
            '--amount' => '50.00',
            '--provider' => 'stripe',
        ]));

        $display = trim($tester->getDisplay());
        self::assertJson($display);
        self::assertStringContainsString('"action":"refund"', $display);
        self::assertStringContainsString('"status":"refunded"', $display);
        self::assertStringContainsString('"providerRef":"stripe_refund_123"', $display);
    }
}
