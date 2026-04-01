<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\ServiceInterface\AdaptiveRoutingInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'payment:routing:adaptive')]
final class PaymentRoutingAdaptiveCommand extends Command
{
    public function __construct(private readonly AdaptiveRoutingInterface $adaptive)
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

        $plan = $this->adaptive->plan($providers, $operation);

        $output->writeln(json_encode([
            'action' => 'routing-adaptive',
            'operation' => $operation,
            'chosen' => $plan['chosen'],
            'weights' => $plan['weights'],
        ], JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
