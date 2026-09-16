<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\ServiceInterface\PaymentDlqServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Replays failed delivery items from the payment DLQ back into the outbox pipeline.
 */
#[AsCommand(name: 'payment:dlq:replay', description: 'Replay DLQ items back to outbox')]
final class PaymentDlqReplayCommand extends Command
{
    public function __construct(
        private readonly PaymentDlqServiceInterface $dlq,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('limit', InputArgument::OPTIONAL, 'Max items to replay', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = max(1, (int) $input->getArgument('limit'));
        $rows = array_slice($this->dlq->list(), 0, $limit);

        foreach ($rows as $row) {
            $this->dlq->replay((int) $row['id']);
        }

        $output->writeln('Replayed: '.count($rows));

        return Command::SUCCESS;
    }
}
