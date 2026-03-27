<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use Random\Engine\Mt19937;
use Random\Randomizer;

final class PaymentFixtureFaker
{
    private readonly Randomizer $randomizer;

    public function __construct(int $seed = 1001)
    {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    public function amount(): string
    {
        $cents = $this->randomizer->getInt(1000, 15000);

        return number_format($cents / 100, 2, '.', '');
    }

    public function providerReference(string $provider): string
    {
        return sprintf('%s_%02d_%06d', strtolower($provider), $this->randomizer->getInt(1, 99), $this->randomizer->getInt(1, 999999));
    }
}
