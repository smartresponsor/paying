<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment\Console;

use App\Service\Payment\ProjectionSync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:projection:rebuild', description: 'Rebuild full projection to MySQL')]
class ProjectionRebuildCommand extends Command
{
    public function __construct(private readonly ProjectionSync $sync) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('batch', InputArgument::OPTIONAL, 'Batch size', '1000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $n = $this->sync->rebuild((int)$input->getArgument('batch'));
        $output->writeln("Rebuilt: {$n}");
        return Command::SUCCESS;
    }
}
