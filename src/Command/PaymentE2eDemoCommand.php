<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:e2e:demo', description: 'Demonstrates E2E flow: webhook -> outbox -> publish -> consume')]
final class PaymentE2eDemoCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>[demo]</info> POST /webhook -> outbox (see tests for full example).');
        $output->writeln('<info>[demo]</info> Run: php bin/console payment:outbox:process');
        $output->writeln('<info>[demo]</info> Then: php bin/console messenger:consume payment_events_in -vv');

        return Command::SUCCESS;
    }
}
