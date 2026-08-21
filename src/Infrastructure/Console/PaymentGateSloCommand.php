<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\Service\PaymentMetric;
use App\Paying\ServiceInterface\PaymentProjectionLagServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reports payment quality gate and SLO posture from stored operational data.
 */
#[AsCommand(name: 'payment:gate:slo', description: 'Fail non-zero failures; print metrics')]
final class PaymentGateSloCommand extends Command
{
    public function __construct(
        private readonly PaymentMetric $metric,
        private readonly PaymentProjectionLagServiceInterface $projectionLag,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $text = $this->metric->export();
        // Fail gate if projection lag > 5000 ms (5s)
        try {
            $snapshot = $this->projectionLag->snapshot();
            if ($snapshot['projectionLagMs'] > 5000) {
                $output->writeln('projection_lag_ms '.$snapshot['projectionLagMs']);

                return Command::FAILURE;
            }
        } catch (\Throwable $throwable) {
            $this->logger->warning('Unable to measure payment projection lag.', ['exception' => $throwable]);
        }

        $output->writeln($text);
        if (preg_match('/payment_failure_total\\s+(\\d+)/', $text, $matches) && (int) $matches[1] > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
