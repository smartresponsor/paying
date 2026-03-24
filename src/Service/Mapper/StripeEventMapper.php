<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Mapper;
use App\ServiceInterface\EventMapperInterface;

class StripeEventMapper implements EventMapperInterface
{
    public function provider(): string
    {
        return 'stripe';
    }

    public function extractPaymentId(array $payload): ?string
    {
        $obj = $payload['data']['object'] ?? null;
        if (is_array($obj) && isset($obj['metadata']['payment'])) {
            return (string) $obj['metadata']['payment'];
        }
        if (is_array($obj) && isset($obj['id'])) {
            return (string) $obj['id'];
        }

        return null;
    }

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
