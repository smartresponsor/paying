<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\ServiceInterface\ProjectionSyncInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:projection:sync', description: 'Sync payment projection')]
class ProjectionSyncCommand extends Command
{
    public function __construct(private readonly ProjectionSyncInterface $sync)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Batch size', '500');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $n = $this->sync->sync((int) $input->getArgument('limit'));
        $output->writeln("Synced: {$n}");

        return Command::SUCCESS;
    }
}
