<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ValueObject\PaymentStatus;

final readonly class PaymentStatusTransitionPolicy
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        'new' => ['new', 'pending', 'processing', 'failed', 'canceled'],
        'pending' => ['pending', 'processing', 'failed', 'canceled', 'completed'],
        'processing' => ['processing', 'completed', 'failed', 'canceled', 'refunded'],
        'completed' => ['completed', 'refunded'],
        'failed' => ['failed', 'processing', 'canceled'],
        'canceled' => ['canceled'],
        'refunded' => ['refunded'],
    ];

    public static function assertCanTransition(PaymentStatus $from, PaymentStatus $to): void
    {
        if (self::canTransition($from, $to)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid payment status transition: %s -> %s.',
            $from->value,
            $to->value,
        ));
    }

    public static function canTransition(PaymentStatus $from, PaymentStatus $to): bool
    {
        return in_array($to->value, self::ALLOWED[$from->value] ?? [], true);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function matrix(): array
    {
        return self::ALLOWED;
    }
}
