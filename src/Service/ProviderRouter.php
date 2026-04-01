<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\PaymentProviderInterface;
use App\ServiceInterface\ProviderRouterInterface;

final class ProviderRouter implements ProviderRouterInterface
{
    /** @var array<string, PaymentProviderInterface> */
    private readonly array $providers;

    /** @param iterable<string, PaymentProviderInterface> $providers */
    public function __construct(iterable $providers)
    {
        $resolvedProviders = [];

        foreach ($providers as $name => $provider) {
            if ($provider instanceof PaymentProviderInterface) {
                $resolvedProviders[(string) $name] = $provider;
            }
        }

        $this->providers = $resolvedProviders;
    }

    public function for(string $provider): PaymentProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new \InvalidArgumentException(sprintf('Unknown payment provider "%s".', $provider));
        }

        return $this->providers[$provider];
    }
}
