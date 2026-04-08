<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Produces deterministic-looking payment fixture values for seed data generation.
 */
final readonly class PaymentFixtureFaker
{
    private Randomizer $randomizer;

    public function __construct(int $seed = 1001)
    {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    /**
     * Generates a fixture amount suitable for payment seed data.
     */
    public function amount(): string
    {
        $cents = $this->randomizer->getInt(1000, 15000);

        return number_format($cents / 100, 2, '.', '');
    }

    /**
     * Generates a provider reference string for seeded payment records.
     */
    public function providerReference(string $provider): string
    {
        return sprintf('%s_%02d_%06d', strtolower($provider), $this->randomizer->getInt(1, 99), $this->randomizer->getInt(1, 999999));
    }
}
