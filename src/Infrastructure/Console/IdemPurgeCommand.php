<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\ServiceInterface\IdempotencyStoreInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Purges expired idempotency entries from the payment operational store.
 */
#[AsCommand(name: 'payment:idem:purge', description: 'Purge expired idempotency entries')]
class IdemPurgeCommand extends Command
{
    public function __construct(private readonly IdempotencyStoreInterface $store)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $purgedCount = $this->store->purgeExpired();
        $output->writeln("Purged: {$purgedCount}");

        return Command::SUCCESS;
    }
}
