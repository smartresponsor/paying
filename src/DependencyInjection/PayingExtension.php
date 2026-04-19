<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Exports the canonical early parameter surface for the Paying component.
 */
final class PayingExtension extends Extension
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('paying.storage.data_server_version', $config['storage']['data_server_version']);
        $container->setParameter('paying.storage.infra_server_version', $config['storage']['infra_server_version']);
        $container->setParameter('paying.messenger.dsn', $config['messenger']['dsn']);
    }

    public function getAlias(): string
    {
        return 'paying';
    }
}
