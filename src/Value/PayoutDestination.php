<?php

declare(strict_types=1);

namespace App\Paying\Value;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class PayoutDestination
{
    public function __construct(
        public string $provider,
        public string $type,
        public string $connectedAccountReference,
        public string $providerReference,
    ) {
        if ('' === trim($this->provider)) {
            throw new \InvalidArgumentException('Payout destination provider must not be empty.');
        }
        if ('' === trim($this->type)) {
            throw new \InvalidArgumentException('Payout destination type must not be empty.');
        }
        if ('' === trim($this->connectedAccountReference)) {
            throw new \InvalidArgumentException('Payout connected account reference must not be empty.');
        }
        if ('' === trim($this->providerReference)) {
            throw new \InvalidArgumentException('Payout destination provider reference must not be empty.');
        }
    }
}
