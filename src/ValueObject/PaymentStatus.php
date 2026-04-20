<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ValueObject;

/**
 * Represents the payment status value object used by the payment lifecycle and related contracts.
 */
enum PaymentStatus: string
{
    case new = 'new';
    case pending = 'pending';
    case processing = 'processing';
    case completed = 'completed';
    case failed = 'failed';
    case canceled = 'canceled';
    case refunded = 'refunded';

    /**
     * Returns the list of allowed payment status values exposed by this enumeration-like object.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
