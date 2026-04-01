<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\ServiceInterface\ProviderScorerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:routing:score')]
final class PaymentRoutingScoreCommand extends Command
{
    public function __construct(private readonly ProviderScorerInterface $scorer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('providers', null, InputOption::VALUE_REQUIRED);
        $this->addOption('operation', null, InputOption::VALUE_REQUIRED, '', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $providers = array_values(array_filter(array_map('trim', explode(',', (string) $input->getOption('providers')))));
        $operation = (string) $input->getOption('operation');

        if ([] === $providers) {
            $output->writeln('<error>providers required</error>');
            return Command::INVALID;
        }

        $ranked = $this->scorer->rank($providers, $operation);
        $chosen = $this->scorer->choose($providers, $operation);

        $output->writeln(json_encode([
            'action' => 'routing-score',
            'operation' => $operation,
            'chosen' => $chosen,
            'ranking' => $ranked,
        ], JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
