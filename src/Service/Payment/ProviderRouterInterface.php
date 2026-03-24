<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface ProviderRouterInterface
{
    public function for(string $provider): PaymentProviderInterface;
}
