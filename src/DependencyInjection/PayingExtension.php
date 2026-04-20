<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Projects processed bundle configuration into the container as the Paying component parameter surface.
 */
final class PayingExtension extends Extension
{
    /**
     * Resolves user configuration and exports the canonical parameters consumed by infrastructure wiring.
     *
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        parent::load($configs, $container);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('paying.storage.data_server_version', $config['storage']['data_server_version']);
        $container->setParameter('paying.storage.infra_server_version', $config['storage']['infra_server_version']);
        $container->setParameter('paying.messenger.dsn', $config['messenger']['dsn']);
    }

    /**
     * Returns the stable bundle alias used by consumer applications in Symfony configuration.
     */
    public function getAlias(): string
    {
        return 'paying';
    }
}
