<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Outbox\PaymentOutboxProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */

/**
 *
 */
#[AsCommand(name: 'payment:outbox:process', description: 'Publish payment outbox messages to RabbitMQ')]
class PaymentOutboxProcessCommand extends Command
{
    /**
     * @param \App\ServiceInterface\Outbox\PaymentOutboxProcessorInterface $processor
     */
    /**
     * @param \App\ServiceInterface\Outbox\PaymentOutboxProcessorInterface $processor
     */
    public function __construct(private readonly readonly PaymentOutboxProcessorInterface $processor)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Max messages per batch', '50')
            ->addOption('retry', null, InputOption::VALUE_NONE, 'Also retry previously failed');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $retry = (bool) $input->getOption('retry');
        $n = $this->processor->process($limit, $retry);
        $output->writeln(sprintf('<info>Published %d message(s)</info>', $n));

        return Command::SUCCESS;
    }
}
