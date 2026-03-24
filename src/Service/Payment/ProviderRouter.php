<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

final class ProviderRouter implements ProviderRouterInterface
{
    /** @var array<string, PaymentProviderInterface> */
    private array $map = [];

    /** @param iterable<string, PaymentProviderInterface> $providers */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $name => $provider) {
            if ($provider instanceof PaymentProviderInterface) {
                $this->map[(string) $name] = $provider;
            }
        }
    }

    public function for(string $provider): PaymentProviderInterface
    {
        if (!isset($this->map[$provider])) {
            throw new \InvalidArgumentException(sprintf('Unknown payment provider "%s".', $provider));
        }

        return $this->map[$provider];
    }
}
