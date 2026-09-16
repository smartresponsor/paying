<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\ServiceInterface\PaymentProviderInterface;
use App\Paying\ServiceInterface\PaymentProviderRouterInterface;

/**
 * Provides the provider router service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentProviderRouter implements PaymentProviderRouterInterface
{
    /** @var array<string, PaymentProviderInterface> */
    private array $providers;

    /** @param iterable<string, PaymentProviderInterface> $providers */
    public function __construct(iterable $providers)
    {
        if (is_array($providers)) {
            $this->providers = $providers;

            return;
        }

        $resolvedProviders = array_map(function ($provider) {
            return $provider;
        }, (array) $providers);

        $this->providers = $resolvedProviders;
    }

    /**
     * Provides the for behavior for the provider router component.
     */
    public function for(string $provider): PaymentProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new \InvalidArgumentException(sprintf('Unknown payment provider "%s".', $provider));
        }

        return $this->providers[$provider];
    }
}
