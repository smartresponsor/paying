<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment\Console;

use App\Infrastructure\Payment\OutboxPublisher;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:dlq:replay', description: 'Replay DLQ items back to outbox')]
class DlqReplayCommand extends Command
{
    public function __construct(
        private readonly Connection $data,
        private readonly OutboxPublisher $outbox
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Max items to replay', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int)$input->getArgument('limit');
        $rows = $this->data->fetchAllAssociative('SELECT * FROM payment_dlq ORDER BY id ASC LIMIT :lim', ['lim'=>$limit]);
        foreach ($rows as $r) {
            $this->outbox->enqueue((string)$r['topic'], json_decode((string)$r['payload'], true) ?? []);
            $this->data->executeStatement('DELETE FROM payment_dlq WHERE id = :id', ['id'=>$r['id']]);
        }
        $output->writeln("Replayed: ".count($rows));
        return Command::SUCCESS;
    }
}
