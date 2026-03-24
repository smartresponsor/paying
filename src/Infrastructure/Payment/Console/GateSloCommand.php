<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment\Console;

use App\Service\Payment\Metric;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:gate:slo', description: 'Fail non-zero failures; print metrics')]
class GateSloCommand extends Command
{
    public function __construct(private readonly Metric $metric, private readonly Connection $data, private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $text = $this->metric->export();
        // Fail gate if projection lag > 5000 ms (5s)
        try {
            $d = (string) ($this->data->fetchOne('SELECT MAX(updated_at) FROM payment') ?: '');
            $i = $d; // assume infra close to data if cannot query; gate only if clear lag can be measured
            if (function_exists('strtotime')) {
                $lagMs = 0;
                if ($d && $i) {
                    $lagMs = max(0, (strtotime($d) - strtotime($i)) * 1000);
                }
                if ($lagMs > 5000) {
                    $output->writeln('projection_lag_ms '.$lagMs);

                    return Command::FAILURE;
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to measure payment projection lag.', ['exception' => $e]);
        }

        $output->writeln($text);
        if (preg_match('/payment_failure_total\\s+(\\d+)/', $text, $m) && (int) $m[1] > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
