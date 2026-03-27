<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\ServiceInterface\SlaReporterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:sla:report', description: 'Print SLA stats for window')]
class SlaReportCommand extends Command
{
    public function __construct(private readonly SlaReporterInterface $sla)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('window', InputArgument::OPTIONAL, 'ISO interval like P1D/PT24H', 'P1D');
    }

    /**
     * @throws \JsonException
     */
    /**
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $win = (string) $input->getArgument('window');
        $data = $this->sla->since($win);
        $output->writeln((string) json_encode($data, JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
