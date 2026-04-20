<?php

declare(strict_types=1);

namespace App\Paying\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines the outward-facing configuration surface for the Paying component.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('paying');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('data_server_version')->defaultValue('16.0')->end()
                        ->scalarNode('infra_server_version')->defaultValue('3.45.1')->end()
                    ->end()
                ->end()
                ->arrayNode('messenger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dsn')->defaultValue('%env(resolve:RABBITMQ_DSN)%')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
