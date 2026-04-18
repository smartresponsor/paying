<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Loads bundle-level Paying container configuration and outward parameters.
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

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/component'));
        $loader->load('services.yaml');

        $container->setParameter('paying.app_secret', $config['app_secret']);
        $container->setParameter('paying.storage.data_url', $config['storage']['data_url']);
        $container->setParameter('paying.storage.data_server_version', $config['storage']['data_server_version']);
        $container->setParameter('paying.storage.infra_url', $config['storage']['infra_url']);
        $container->setParameter('paying.storage.infra_server_version', $config['storage']['infra_server_version']);
        $container->setParameter('paying.messenger.dsn', $config['messenger']['dsn']);
        $container->setParameter('paying.provider.default_provider', $config['provider']['default_provider']);
        $container->setParameter('paying.provider.stripe_secret_key', $config['provider']['stripe_secret_key']);
        $container->setParameter('paying.provider.stripe_webhook_secret', $config['provider']['stripe_webhook_secret']);
        $container->setParameter('paying.provider.payment_success_url', $config['provider']['payment_success_url']);
        $container->setParameter('paying.provider.payment_cancel_url', $config['provider']['payment_cancel_url']);
        $container->setParameter('paying.idempotency.ttl_sec', $config['idempotency']['ttl_sec']);
        $container->setParameter('paying.idempotency.redis_url', $config['idempotency']['redis_url']);
        $container->setParameter('paying.oidc.disabled', $config['oidc']['disabled']);
        $container->setParameter('paying.oidc.issuer', $config['oidc']['issuer']);
        $container->setParameter('paying.oidc.audience', $config['oidc']['audience']);
        $container->setParameter('paying.oidc.jwks_url', $config['oidc']['jwks_url']);
        $container->setParameter('paying.oidc.jwks_ttl', $config['oidc']['jwks_ttl']);
        $container->setParameter('paying.webhook.allow_unknown', $config['webhook']['allow_unknown']);
        $container->setParameter('paying.webhook.adyen_hmac_secret', $config['webhook']['adyen_hmac_secret']);
    }

    public function getAlias(): string
    {
        return 'paying';
    }
}
