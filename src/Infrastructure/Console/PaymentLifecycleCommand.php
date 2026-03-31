<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentServiceInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ServiceInterface\ProjectionLagServiceInterface;
use App\ServiceInterface\ProviderGuardInterface;
use App\ServiceInterface\RefundServiceInterface;
use App\ValueObject\PaymentStatus;
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
        private readonly ProjectionLagServiceInterface $projectionLagService,
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
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, '', '50');
        $this->addOption('failed-threshold', null, InputOption::VALUE_OPTIONAL, '', '1');
        $this->addOption('lag-threshold-ms', null, InputOption::VALUE_OPTIONAL, '', '60000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = strtolower(trim((string) $input->getOption('action')));

        return match ($action) {
            'create' => $this->runCreate($input, $output),
            'start' => $this->runStart($input, $output),
            'restart-failed' => $this->runRestartFailed($input, $output),
            'alerts' => $this->runAlerts($input, $output),
            'finalize' => $this->runFinalize($input, $output),
            'refund' => $this->runRefund($input, $output),
            default => $this->invalidAction($output, $action),
        };
    }

    private function runStart(InputInterface $input, OutputInterface $output): int
    {
        $paymentId = trim((string) $input->getOption('payment-id'));
        $provider = trim((string) $input->getOption('provider'));
        $idempotencyKey = (string) $input->getOption('idempotency-key');
        $origin = (string) $input->getOption('origin');

        if ('' !== $paymentId) {
            if (!Ulid::isValid($paymentId)) {
                $output->writeln('<error>start restart requires --payment-id as ULID.</error>');

                return Command::INVALID;
            }

            $result = $this->paymentStartService->restart($paymentId, $provider, $idempotencyKey, $origin);

            $this->writeResult($output, [
                'action' => 'start',
                'mode' => 'restart',
                'id' => (string) $result->payment->id(),
                'status' => $result->payment->status()->value,
                'providerRef' => $result->providerRef,
            ]);

            return Command::SUCCESS;
        }

        $amount = trim((string) $input->getOption('amount'));
        $currency = strtoupper(trim((string) $input->getOption('currency')));

        if ('' === $provider || '' === $amount || '' === $currency) {
            $output->writeln('<error>start requires --provider, --amount, and --currency.</error>');

            return Command::INVALID;
        }

        $started = $this->paymentStartService->start($provider, $amount, $currency, $idempotencyKey, $origin);

        $this->writeResult($output, [
            'action' => 'start',
            'mode' => 'create',
            'id' => (string) $started->payment->id(),
            'status' => $started->payment->status()->value,
            'providerRef' => $started->providerRef,
        ]);

        return Command::SUCCESS;
    }

    private function runRestartFailed(InputInterface $input, OutputInterface $output): int
    {
        $provider = trim((string) $input->getOption('provider'));
        $origin = (string) $input->getOption('origin');
        $limit = max(1, (int) $input->getOption('limit'));

        $paymentIds = $this->paymentRepository->listIdsByStatuses([PaymentStatus::failed->value], $limit);
        $restarted = 0;
        $failed = [];

        foreach ($paymentIds as $paymentId) {
            try {
                $this->paymentStartService->restart((string) $paymentId, $provider, '', $origin);
                ++$restarted;
            } catch (\Throwable) {
                $failed[] = (string) $paymentId;
            }
        }

        $this->writeResult($output, [
            'action' => 'restart-failed',
            'provider' => $provider,
            'processed' => count($paymentIds),
            'restarted' => $restarted,
            'failed' => count($failed),
            'failedIds' => $failed,
        ]);

        return 0 === count($failed) ? Command::SUCCESS : Command::FAILURE;
    }

    private function runAlerts(InputInterface $input, OutputInterface $output): int
    {
        $failedThreshold = max(0, (int) $input->getOption('failed-threshold'));
        $lagThresholdMs = max(0, (int) $input->getOption('lag-threshold-ms'));

        $failedIds = $this->paymentRepository->listIdsByStatuses([PaymentStatus::failed->value], $failedThreshold + 1);
        $snapshot = $this->projectionLagService->snapshot();

        $checks = [
            'failedPayments' => [
                'threshold' => $failedThreshold,
                'value' => count($failedIds),
                'triggered' => count($failedIds) > $failedThreshold,
            ],
            'projectionLagMs' => [
                'threshold' => $lagThresholdMs,
                'value' => $snapshot['projectionLagMs'],
                'triggered' => $snapshot['projectionLagMs'] > $lagThresholdMs,
            ],
        ];

        $triggered = array_filter($checks, static fn (array $check): bool => true === $check['triggered']);

        $this->writeResult($output, [
            'action' => 'alerts',
            'status' => [] === $triggered ? 'ok' : 'alert',
            'checks' => $checks,
            'failedIdsSample' => $failedIds,
        ]);

        return [] === $triggered ? Command::SUCCESS : Command::FAILURE;
    }

    private function runCreate(InputInterface $input, OutputInterface $output): int
    {
        $orderId = (string) $input->getOption('order-id');
        $amountMinor = (int) $input->getOption('amount-minor');
        $currency = strtoupper((string) $input->getOption('currency'));

        if ('' === $orderId || $amountMinor <= 0 || '' === $currency) {
            $output->writeln('<error>create requires --order-id, --amount-minor (>0), and --currency.</error>');

            return Command::INVALID;
        }

        $payment = $this->paymentService->create($orderId, $amountMinor, $currency);

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
        $provider = (string) $input->getOption('provider');

        if (!Ulid::isValid($paymentId) || '' === $provider) {
            $output->writeln('<error>finalize requires --payment-id (ULID) and --provider.</error>');

            return Command::INVALID;
        }

        $existing = $this->paymentRepository->find($paymentId);
        if (null === $existing) {
            $output->writeln(sprintf('<error>Payment %s was not found.</error>', $paymentId));

            return Command::FAILURE;
        }

        $resolved = $this->providerGuard->finalize(
            $provider,
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
        $paymentId = (string) $input->getOption('payment-id');
        $amount = (string) $input->getOption('amount');
        $provider = (string) $input->getOption('provider');

        if (!Ulid::isValid($paymentId) || '' === $amount || '' === $provider) {
            $output->writeln('<error>refund requires --payment-id (ULID), --amount, and --provider.</error>');

            return Command::INVALID;
        }

        $payment = $this->refundService->refund(new Ulid($paymentId), $amount, $provider);

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
        $output->writeln((string) json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
