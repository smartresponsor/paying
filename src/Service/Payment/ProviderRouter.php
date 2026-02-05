<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\ProviderRouterInterface;
use App\ServiceInterface\Payment\GatewayPortInterface;
use InvalidArgumentException;

class ProviderRouter implements ProviderRouterInterface
{
    /** @var array<string,GatewayPortInterface> */
    private array $map;

    public function __construct(iterable $adapters)
    {
        $this->map = [];
        foreach ($adapters as $name => $adapter) {
            if ($adapter instanceof GatewayPortInterface) {
                $this->map[(string)$name] = $adapter;
            }
        }
    }

    public function for(string $provider): GatewayPortInterface
    {
        if (!isset($this->map[$provider])) {
            throw new InvalidArgumentException("Unknown provider: {$provider}");
        }
        return $this->map[$provider];
    }
}
