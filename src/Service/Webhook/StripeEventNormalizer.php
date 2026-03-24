<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Webhook;

final class StripeEventNormalizer
{
    public function routingKey(array $payload): string
    {
        return match ((string) ($payload['type'] ?? '')) {
            'payment_intent.succeeded' => 'payment.captured',
            'charge.refunded', 'payment_intent.canceled' => 'payment.refunded',
            'payment_intent.payment_failed' => 'payment.failed',
            default => 'payment.webhook.received',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(array $payload): array
    {
        $object = is_array($payload['data']['object'] ?? null) ? $payload['data']['object'] : [];
        $metadata = is_array($object['metadata'] ?? null) ? $object['metadata'] : [];
        $amountMinor = $this->extractAmountMinor($object);

        return [
            'paymentId' => $this->firstNonEmpty([
                $metadata['payment'] ?? null,
                $metadata['paymentId'] ?? null,
                $metadata['payment_id'] ?? null,
            ]),
            'orderId' => $this->firstNonEmpty([
                $metadata['order'] ?? null,
                $metadata['orderId'] ?? null,
                $metadata['order_id'] ?? null,
                $object['client_reference_id'] ?? null,
            ]),
            'amountMinor' => $amountMinor,
            'currency' => strtoupper((string) ($object['currency'] ?? 'USD')),
            'gatewayTransactionId' => $this->firstNonEmpty([
                $object['latest_charge'] ?? null,
                $object['id'] ?? null,
                $payload['id'] ?? null,
            ]),
            'reason' => $this->firstNonEmpty([
                $object['last_payment_error']['code'] ?? null,
                $object['cancellation_reason'] ?? null,
            ]),
            'errorCode' => (string) ($object['last_payment_error']['code'] ?? ''),
            'message' => $this->firstNonEmpty([
                $object['last_payment_error']['message'] ?? null,
                $payload['type'] ?? null,
            ]),
            'externalEventId' => (string) ($payload['id'] ?? ''),
            'raw' => $payload,
        ];
    }

    private function extractAmountMinor(array $object): int
    {
        foreach (['amount_received', 'amount_total', 'amount_capturable', 'amount'] as $field) {
            if (isset($object[$field]) && is_numeric($object[$field])) {
                return (int) $object[$field];
            }
        }

        return 0;
    }

    /**
     * @param list<mixed> $values
     */
    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            $normalized = trim((string) $value);
            if ('' !== $normalized) {
                return $normalized;
            }
        }

        return null;
    }
}
