<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment\Mapper;
use App\ServiceInterface\Payment\EventMapperInterface;

class AdyenEventMapper implements EventMapperInterface
{
    public function provider(): string
    {
        return 'adyen';
    }

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
