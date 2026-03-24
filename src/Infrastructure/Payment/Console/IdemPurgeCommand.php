<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment\Console;
use App\ServiceInterface\Payment\IdempotencyStoreInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:idem:purge', description: 'Purge expired idempotency entries')]
class IdemPurgeCommand extends Command
{
    public function __construct(private readonly IdempotencyStoreInterface $store)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $n = $this->store->purgeExpired();
        $output->writeln("Purged: {$n}");

        return Command::SUCCESS;
    }
}
