<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

final class PaymentNotFoundException extends \RuntimeException
{
    public static function byId(string $paymentId): self
    {
        return new self('Payment not found: '.$paymentId);
    }
}
