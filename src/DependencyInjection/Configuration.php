<?php

declare(strict_types=1);

namespace App\DependencyInjection;

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
                ->scalarNode('app_secret')->defaultValue('%env(APP_SECRET)%')->end()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('data_url')->defaultValue('%env(resolve:DATABASE_URL)%')->end()
                        ->scalarNode('data_server_version')->defaultValue('16.0')->end()
                        ->scalarNode('infra_url')->defaultValue('%env(resolve:INFRA_URL)%')->end()
                        ->scalarNode('infra_server_version')->defaultValue('3.45.1')->end()
                    ->end()
                ->end()
                ->arrayNode('messenger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dsn')->defaultValue('%env(resolve:RABBITMQ_DSN)%')->end()
                    ->end()
                ->end()
                ->arrayNode('provider')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_provider')->defaultValue('manual')->end()
                        ->scalarNode('stripe_secret_key')->defaultValue('%env(default::STRIPE_SECRET_KEY)%')->end()
                        ->scalarNode('stripe_webhook_secret')->defaultValue('%env(default::STRIPE_WEBHOOK_SECRET)%')->end()
                        ->scalarNode('payment_success_url')->defaultValue('%env(default::PAYMENT_SUCCESS_URL)%')->end()
                        ->scalarNode('payment_cancel_url')->defaultValue('%env(default::PAYMENT_CANCEL_URL)%')->end()
                    ->end()
                ->end()
                ->arrayNode('idempotency')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('ttl_sec')->defaultValue(86400)->end()
                        ->scalarNode('redis_url')->defaultValue('%env(default::REDIS_URL)%')->end()
                    ->end()
                ->end()
                ->arrayNode('oidc')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('disabled')->defaultFalse()->end()
                        ->scalarNode('issuer')->defaultValue('%env(default::OIDC_ISS)%')->end()
                        ->scalarNode('audience')->defaultValue('%env(default::OIDC_AUD)%')->end()
                        ->scalarNode('jwks_url')->defaultValue('%env(default::OIDC_JWKS_URL)%')->end()
                        ->integerNode('jwks_ttl')->defaultValue(3600)->end()
                    ->end()
                ->end()
                ->arrayNode('webhook')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_unknown')->defaultFalse()->end()
                        ->scalarNode('adyen_hmac_secret')->defaultValue('%env(default::ADYEN_HMAC_SECRET)%')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
