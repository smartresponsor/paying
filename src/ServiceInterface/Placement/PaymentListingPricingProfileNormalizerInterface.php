<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface\Placement;

interface PaymentListingPricingProfileNormalizerInterface
{
    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function normalize(string $retailKind, array $input): array;
}
