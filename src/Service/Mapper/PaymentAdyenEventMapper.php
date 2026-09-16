<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service\Mapper;

use App\Paying\ServiceInterface\EventMapperInterface;

/**
 * Maps external provider payloads into the internal adyen event mapper payment representation.
 */
class PaymentAdyenEventMapper implements EventMapperInterface
{
    /**
     * Provides the provider behavior for the adyen event mapper component.
     */
    public function provider(): string
    {
        return 'adyen';
    }

    /**
     * Provides the extract payment id behavior for the adyen event mapper component.
     */
    public function extractPaymentId(array $payload): ?string
    {
        if (isset($payload['additionalData']['merchantReference'])) {
            return (string) $payload['additionalData']['merchantReference'];
        }
        if (isset($payload['pspReference'])) {
            return (string) $payload['pspReference'];
        }

        return null;
    }

    /**
     * Provides the map status behavior for the adyen event mapper component.
     */
    public function mapStatus(array $payload): ?string
    {
        $eventCode = (string) ($payload['eventCode'] ?? '');

        return match ($eventCode) {
            'AUTHORISATION' => 'processing',
            'CAPTURE' => 'completed',
            'REFUND' => 'refunded',
            'CANCELLATION', 'CANCEL_OR_REFUND' => 'canceled',
            default => null,
        };
    }
}
