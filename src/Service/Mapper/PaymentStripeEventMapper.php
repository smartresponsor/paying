<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Mapper;

use App\Paying\ServiceInterface\EventMapperInterface;

/**
 * Maps external provider payloads into the internal stripe event mapper payment representation.
 */
class PaymentStripeEventMapper implements EventMapperInterface
{
    /**
     * Provides the provider behavior for the stripe event mapper component.
     */
    public function provider(): string
    {
        return 'stripe';
    }

    /**
     * Provides the extract payment id behavior for the stripe event mapper component.
     */
    public function extractPaymentId(array $payload): ?string
    {
        $object = $payload['data']['object'] ?? null;
        if (is_array($object) && isset($object['metadata']['payment'])) {
            return (string) $object['metadata']['payment'];
        }
        if (is_array($object) && isset($object['id'])) {
            return (string) $object['id'];
        }

        return null;
    }

    /**
     * Provides the map status behavior for the stripe event mapper component.
     */
    public function mapStatus(array $payload): ?string
    {
        $type = (string) ($payload['type'] ?? '');

        return match ($type) {
            'payment_intent.succeeded' => 'completed',
            'payment_intent.payment_failed' => 'failed',
            default => null,
        };
    }
}
