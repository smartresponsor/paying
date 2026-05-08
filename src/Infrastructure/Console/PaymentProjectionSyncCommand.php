<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\ServiceInterface\PaymentProjectionSyncServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronizes payment projection records with the current aggregate state.
 */
#[AsCommand(name: 'payment:projection:sync', description: 'Sync payment projection')]
final class PaymentProjectionSyncCommand extends Command
{
    public function __construct(private readonly PaymentProjectionSyncServiceInterface $sync)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('limit', InputArgument::OPTIONAL, 'Batch size', '500');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $syncedCount = $this->sync->sync((int) $input->getArgument('limit'));
        $output->writeln("Synced: {$syncedCount}");

        return Command::SUCCESS;
    }
}
