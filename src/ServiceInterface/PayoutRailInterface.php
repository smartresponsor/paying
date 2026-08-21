<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Value\PayoutDestination;

interface PayoutRailInterface
{
    public function supports(PayoutDestination $destination): bool;

    public function submit(PayoutDestination $destination, int $amountMinor, string $currency, string $idempotencyKey): string;

    public function supportsReference(string $railReference): bool;

    public function compensateFailure(string $railReference, string $idempotencyKey): void;

    public function reverse(string $railReference, string $idempotencyKey): void;
}
