<?php

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

#[AsCommand(name: 'payment:lifecycle:run')]
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
        $this->addOption('action', null, InputOption::VALUE_REQUIRED);
        $this->addOption('payment-id', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('order-id', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('amount-minor', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('amount', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('currency', null, InputOption::VALUE_OPTIONAL, '', 'USD');
        $this->addOption('provider', null, InputOption::VALUE_OPTIONAL, '', 'internal');
        $this->addOption('origin', null, InputOption::VALUE_OPTIONAL, '', 'cli');
        $this->addOption('idempotency-key', null, InputOption::VALUE_OPTIONAL, '', '');
        $this->addOption('provider-ref', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('gateway-transaction-id', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('status', null, InputOption::VALUE_OPTIONAL);
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

    private function runStart(InputInterface $input, OutputInterface $output): int
    {
        $paymentId = trim((string) $input->getOption('payment-id'));
        $provider = trim((string) $input->getOption('provider'));

        if ('' !== $paymentId && Ulid::isValid($paymentId)) {
            $result = $this->paymentStartService->restart(
                $paymentId,
                $provider,
                (string) $input->getOption('idempotency-key'),
                (string) $input->getOption('origin')
            );

            $this->writeResult($output, [
                'action' => 'start',
                'mode' => 'restart',
                'id' => (string) $result->payment->id(),
                'status' => $result->payment->status()->value,
            ]);

            return Command::SUCCESS;
        }

        $amount = trim((string) $input->getOption('amount'));
        $currency = strtoupper(trim((string) $input->getOption('currency')));

        $started = $this->paymentStartService->start($provider, $amount, $currency);

        $this->writeResult($output, [
            'action' => 'start',
            'id' => (string) $started->payment->id(),
            'status' => $started->payment->status()->value,
            'providerRef' => $started->providerRef,
        ]);

        return Command::SUCCESS;
    }

    private function runCreate(InputInterface $input, OutputInterface $output): int
    {
        $payment = $this->paymentService->create(
            (string) $input->getOption('order-id'),
            (int) $input->getOption('amount-minor'),
            strtoupper((string) $input->getOption('currency'))
        );

        $this->writeResult($output, [
            'action' => 'create',
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
        ]);

        return Command::SUCCESS;
    }

    private function runFinalize(InputInterface $input, OutputInterface $output): int
    {
        $paymentId = (string) $input->getOption('payment-id');
        $existing = $this->paymentRepository->find($paymentId);

        $resolved = $this->providerGuard->finalize(
            (string) $input->getOption('provider'),
            new Ulid($paymentId),
            array_filter([
                'providerRef' => (string) $input->getOption('provider-ref'),
                'gatewayTransactionId' => (string) $input->getOption('gateway-transaction-id'),
                'status' => (string) $input->getOption('status'),
            ])
        );

        $existing->syncFrom($resolved);
        $this->paymentRepository->save($existing);

        $this->writeResult($output, [
            'action' => 'finalize',
            'id' => (string) $existing->id(),
            'status' => $existing->status()->value,
        ]);

        return Command::SUCCESS;
    }

    private function runRefund(InputInterface $input, OutputInterface $output): int
    {
        $payment = $this->refundService->refund(
            new Ulid((string) $input->getOption('payment-id')),
            (string) $input->getOption('amount'),
            (string) $input->getOption('provider')
        );

        $this->writeResult($output, [
            'action' => 'refund',
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
        ]);

        return Command::SUCCESS;
    }

    private function invalidAction(OutputInterface $output, string $action): int
    {
        $output->writeln("invalid action: $action");
        return Command::INVALID;
    }

    private function writeResult(OutputInterface $output, array $payload): void
    {
        $output->writeln(json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
