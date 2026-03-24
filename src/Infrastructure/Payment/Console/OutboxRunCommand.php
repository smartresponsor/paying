<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment\Console;

use App\Infrastructure\Payment\OutboxWorker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:outbox:run', description: 'Publish outbox entries')]
class OutboxRunCommand extends Command
{
    public function __construct(private readonly OutboxWorker $worker)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Max items', '100');
        $this->addOption('retry-failed', null, InputOption::VALUE_NONE, 'Also retry failed items before DLQ threshold');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getArgument('limit');
        $retryFailed = (bool) $input->getOption('retry-failed');
        $count = $this->worker->run($limit, $retryFailed);
        $output->writeln(sprintf('Published: %d', $count));

        return Command::SUCCESS;
    }
}
