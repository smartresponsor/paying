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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = (string) $input->getOption('action');

        if ('start' === $action) {
            $paymentId = (string) $input->getOption('payment-id');

            if ('' !== $paymentId && Ulid::isValid($paymentId)) {
                $result = $this->paymentStartService->restart(
                    $paymentId,
                    (string) $input->getOption('provider'),
                    (string) $input->getOption('idempotency-key'),
                    (string) $input->getOption('origin')
                );

                $output->writeln(json_encode(['action' => 'start', 'mode' => 'restart', 'id' => (string) $result->payment->id()]));

                return Command::SUCCESS;
            }
        }

        return Command::SUCCESS;
    }
}
