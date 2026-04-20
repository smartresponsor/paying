<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\ServiceInterface\ProjectionSyncInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Rebuilds payment read models from persisted payment state.
 */
#[AsCommand(name: 'payment:projection:rebuild', description: 'Rebuild full payment projection')]
class ProjectionRebuildCommand extends Command
{
    public function __construct(private readonly ProjectionSyncInterface $sync)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('batch', InputArgument::OPTIONAL, 'Batch size', '1000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rebuiltCount = $this->sync->rebuild((int) $input->getArgument('batch'));
        $output->writeln("Rebuilt: {$rebuiltCount}");

        return Command::SUCCESS;
    }
}
