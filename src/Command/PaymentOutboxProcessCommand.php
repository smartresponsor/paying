<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Command;

use App\Paying\ServiceInterface\Outbox\PaymentOutboxProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the payment outbox publisher from the console for local operations and scheduled jobs.
 *
 * The command delegates batching and retry behavior to the outbox processor and reports the
 * number of published messages back to the operator.
 */
#[AsCommand(name: 'payment:outbox:process', description: 'Publish payment outbox messages to RabbitMQ')]
class PaymentOutboxProcessCommand extends Command
{
    public function __construct(private readonly PaymentOutboxProcessorInterface $processor)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Max messages per batch', '50')
            ->addOption('retry', null, InputOption::VALUE_NONE, 'Also retry previously failed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $retry = (bool) $input->getOption('retry');
        $n = $this->processor->process($limit, $retry);
        $output->writeln(sprintf('<info>Published %d message(s)</info>', $n));

        return Command::SUCCESS;
    }
}
