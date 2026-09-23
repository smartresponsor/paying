<?php

declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\ValueObject\PaymentPayoutDestination;

interface PaymentPayoutRailInterface
{
    public function supports(PaymentPayoutDestination $destination): bool;

    public function submit(PaymentPayoutDestination $destination, int $amountMinor, string $currency, string $idempotencyKey): string;

    public function supportsReference(string $railReference): bool;

    public function compensateFailure(string $railReference, string $idempotencyKey): void;

    public function reverse(string $railReference, string $idempotencyKey): void;
}
