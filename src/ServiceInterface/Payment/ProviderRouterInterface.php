<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

use App\ServiceInterface\Payment\GatewayPortInterface;

interface ProviderRouterInterface
{
    public function for(string $provider): GatewayPortInterface;
}
