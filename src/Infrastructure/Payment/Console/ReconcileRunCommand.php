<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment\Console;

use App\Service\Payment\ReconciliationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Ulid;

#[AsCommand(name: 'payment:reconcile:run', description: 'Reconcile all processing payments')]
class ReconcileRunCommand extends Command
{
    public function __construct(private readonly ReconciliationService $svc) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = $this->svc->listProcessingIds(200);
        $n = 0;
        foreach ($ids as $id) {
            $this->svc->reconcile(new Ulid($id));
            $n++;
        }
        $output->writeln("Reconciled: {$n}");
        return Command::SUCCESS;
    }
}
