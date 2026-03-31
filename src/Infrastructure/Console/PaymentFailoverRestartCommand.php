<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentStartServiceInterface;
use App\ValueObject\PaymentStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:failover:restart')]
final class PaymentFailoverRestartCommand extends Command
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentStartServiceInterface $paymentStartService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED);
        $this->addOption('fallback-provider', null, InputOption::VALUE_REQUIRED);
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, '', '50');
        $this->addOption('origin', null, InputOption::VALUE_OPTIONAL, '', 'cli-failover');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = trim((string) $input->getOption('provider'));
        $fallbackProvider = trim((string) $input->getOption('fallback-provider'));
        $limit = max(1, (int) $input->getOption('limit'));
        $origin = (string) $input->getOption('origin');

        if ('' === $provider || '' === $fallbackProvider || $provider === $fallbackProvider) {
            $output->writeln('<error>failover restart requires distinct --provider and --fallback-provider.</error>');

            return Command::INVALID;
        }

        $paymentIds = $this->paymentRepository->listIdsByStatuses([PaymentStatus::failed->value], $limit);
        $restarted = 0;
        $failed = [];

        foreach ($paymentIds as $paymentId) {
            try {
                $this->paymentStartService->restart((string) $paymentId, $provider, '', $origin);
                ++$restarted;
                continue;
            } catch (\Throwable) {
            }

            try {
                $this->paymentStartService->restart((string) $paymentId, $fallbackProvider, '', $origin);
                ++$restarted;
            } catch (\Throwable) {
                $failed[] = (string) $paymentId;
            }
        }

        $output->writeln((string) json_encode([
            'action' => 'failover-restart',
            'provider' => $provider,
            'fallbackProvider' => $fallbackProvider,
            'processed' => count($paymentIds),
            'restarted' => $restarted,
            'failed' => count($failed),
            'failedIds' => $failed,
        ], JSON_THROW_ON_ERROR));

        return [] === $failed ? Command::SUCCESS : Command::FAILURE;
    }
}
