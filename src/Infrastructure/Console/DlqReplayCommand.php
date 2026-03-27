<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\InfrastructureInterface\OutboxPublisherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
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
        private readonly OutboxPublisherInterface $outbox,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Max items to replay', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = max(1, (int) $input->getArgument('limit'));
        $rows = $this->data->fetchAllAssociative(sprintf('SELECT * FROM payment_dlq ORDER BY id ASC LIMIT %d', $limit));

        foreach ($rows as $row) {
            $this->outbox->enqueue((string) $row['topic'], json_decode((string) $row['payload'], true) ?? []);
            $this->data->executeStatement(
                'DELETE FROM payment_dlq WHERE id = :id',
                ['id' => (int) $row['id']],
                ['id' => ParameterType::INTEGER],
            );
        }

        $output->writeln('Replayed: '.count($rows));

        return Command::SUCCESS;
    }
}
