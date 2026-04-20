<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Console;

use App\Paying\ServiceInterface\ReconciliationServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Runs payment reconciliation routines from the console surface.
 */
#[AsCommand(name: 'payment:reconcile:run', description: 'Reconcile all processing payments')]
class ReconcileRunCommand extends Command
{
    public function __construct(private readonly ReconciliationServiceInterface $svc)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = $this->svc->listProcessingIds(200);
        $reconciledCount = 0;
        foreach ($ids as $id) {
            $this->svc->reconcile(new Ulid($id));
            ++$reconciledCount;
        }
        $output->writeln("Reconciled: {$reconciledCount}");

        return Command::SUCCESS;
    }
}
