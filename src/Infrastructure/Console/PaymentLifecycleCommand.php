<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Ulid;

#[AsCommand(name: 'payment:lifecycle:run', description: 'Run business payment lifecycle actions (create/start/finalize/refund).')]
final class PaymentLifecycleCommand extends Command
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
        private readonly PaymentStartServiceInterface $paymentStartService,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ProviderGuardInterface $providerGuard,
        private readonly RefundServiceInterface $refundService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('action', null, InputOption::VALUE_REQUIRED, 'create|start|finalize|refund');
        $this->addOption('payment-id', null, InputOption::VALUE_REQUIRED, 'Payment ULID for finalize/refund');
        $this->addOption('order-id', null, InputOption::VALUE_REQUIRED, 'Business order id for create');
        $this->addOption('amount-minor', null, InputOption::VALUE_REQUIRED, 'Amount in minor units for create');
        $this->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Amount as decimal string for start/refund');
        $this->addOption('currency', null, InputOption::VALUE_REQUIRED, 'ISO currency code for create/start', 'USD');
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider name for start/finalize/refund', 'internal');
        $this->addOption('provider-ref', null, InputOption::VALUE_REQUIRED, 'Provider reference for finalize');
        $this->addOption('gateway-transaction-id', null, InputOption::VALUE_REQUIRED, 'Gateway transaction id for finalize');
        $this->addOption('status', null, InputOption::VALUE_REQUIRED, 'Provider status for finalize');
        $this->addOption('origin', null, InputOption::VALUE_REQUIRED, 'Origin tag for start', 'cli');
        $this->addOption('idempotency-key', null, InputOption::VALUE_REQUIRED, 'Idempotency key for start', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = strtolower(trim((string) $input->getOption('action')));

        return match ($action) {
            'create' => $this->runCreate($input, $output),
            'start' => $this->runStart($input, $output),
            'finalize' => $this->runFinalize($input, $output),
            'refund' => $this->runRefund($input, $output),
            default => $this->invalidAction($output, $action),
        };
    }

    private function runCreate(InputInterface $input, OutputInterface $output): int
    {
        $orderId = trim((string) $input->getOption('order-id'));
        $amountMinor = (int) $input->getOption('amount-minor');
        $currency = strtoupper(trim((string) $input->getOption('currency')));

        if ('' === $orderId || $amountMinor <= 0 || '' === $currency) {
            $output->writeln('<error>create requires --order-id, --amount-minor (>0), and --currency.</error>');

            return Command::INVALID;
        }

        $payment = $this->paymentService->create($orderId, $amountMinor, $currency);
        try {
            $this->writeResult($output, [
                'action' => 'create',
                'id' => (string) $payment->id(),
                'status' => $payment->status()->value,
                'amount' => $payment->amount(),
                'currency' => $payment->currency(),
            ]);
        } catch (\Exception $e) {
        }

        return Command::SUCCESS;
    }

    private function runStart(InputInterface $input, OutputInterface $output): int
    {
        $provider = trim((string) $input->getOption('provider'));
        $amount = trim((string) $input->getOption('amount'));
        $currency = strtoupper(trim((string) $input->getOption('currency')));
        $idempotencyKey = trim((string) $input->getOption('idempotency-key'));
        $origin = trim((string) $input->getOption('origin'));

        if ('' === $provider || '' === $amount || '' === $currency) {
            $output->writeln('<error>start requires --provider, --amount, and --currency.</error>');

            return Command::INVALID;
        }

        $started = $this->paymentStartService->start($provider, $amount, $currency, $idempotencyKey, $origin);
        $payment = $started->payment;
        try {
            $this->writeResult($output, [
                'action' => 'start',
                'id' => (string) $payment->id(),
                'status' => $payment->status()->value,
                'providerRef' => $started->providerRef,
            ]);
        } catch (\Exception $e) {
        }

        return Command::SUCCESS;
    }

    private function runFinalize(InputInterface $input, OutputInterface $output): int
    {
        $paymentId = trim((string) $input->getOption('payment-id'));
        $provider = trim((string) $input->getOption('provider'));

        if (!Ulid::isValid($paymentId) || '' === $provider) {
            $output->writeln('<error>finalize requires --payment-id (ULID) and --provider.</error>');

            return Command::INVALID;
        }

        $existing = $this->paymentRepository->find($paymentId);
        if (null === $existing) {
            $output->writeln(sprintf('<error>Payment %s was not found.</error>', $paymentId));

            return Command::FAILURE;
        }

        $payload = array_filter([
            'providerRef' => trim((string) $input->getOption('provider-ref')),
            'gatewayTransactionId' => trim((string) $input->getOption('gateway-transaction-id')),
            'status' => trim((string) $input->getOption('status')),
        ], static fn (mixed $value): bool => is_string($value) && '' !== $value);

        $resolved = $this->providerGuard->finalize($provider, new Ulid($paymentId), $payload);
        $existing->syncFrom($resolved);
        $this->paymentRepository->save($existing);

        try {
            $this->writeResult($output, [
                'action' => 'finalize',
                'id' => (string) $existing->id(),
                'status' => $existing->status()->value,
                'providerRef' => $existing->providerRef(),
            ]);
        } catch (\Exception $e) {
        }

        return Command::SUCCESS;
    }

    private function runRefund(InputInterface $input, OutputInterface $output): int
    {
        $paymentId = trim((string) $input->getOption('payment-id'));
        $amount = trim((string) $input->getOption('amount'));
        $provider = trim((string) $input->getOption('provider'));

        if (!Ulid::isValid($paymentId) || '' === $amount || '' === $provider) {
            $output->writeln('<error>refund requires --payment-id (ULID), --amount, and --provider.</error>');

            return Command::INVALID;
        }

        try {
            $payment = $this->refundService->refund(new Ulid($paymentId), $amount, $provider);
        } catch (\RuntimeException $exception) {
            $output->writeln(sprintf('<error>Payment %s was not found.</error>', $paymentId));

            return Command::FAILURE;
        }

        try {
            $this->writeResult($output, [
                'action' => 'refund',
                'id' => (string) $payment->id(),
                'status' => $payment->status()->value,
                'amount' => $payment->amount(),
                'currency' => $payment->currency(),
                'providerRef' => $payment->providerRef(),
            ]);
        } catch (\Exception $e) {
        }

        return Command::SUCCESS;
    }

    private function invalidAction(OutputInterface $output, string $action): int
    {
        $output->writeln(sprintf('<error>Unknown --action "%s". Use create|start|finalize|refund.</error>', $action));

        return Command::INVALID;
    }

    private function writeResult(OutputInterface $output, array $payload): void
    {
        try {
            $output->writeln((string) json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
        }
    }
}
