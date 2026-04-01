<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\ServiceInterface\ProviderSelectorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:routing:choose')]
final class PaymentRoutingChooseCommand extends Command
{
    public function __construct(private readonly ProviderSelectorInterface $selector)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('providers', null, InputOption::VALUE_REQUIRED, 'comma separated providers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $providers = array_values(array_filter(array_map('trim', explode(',', (string) $input->getOption('providers')))));

        if ([] === $providers) {
            $output->writeln('<error>providers required</error>');
            return Command::INVALID;
        }

        $chosen = $this->selector->choose($providers);
        $explain = $this->selector->explain($providers);

        $output->writeln(json_encode([
            'action' => 'routing-choose',
            'chosen' => $chosen,
            'explain' => $explain,
        ], JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
