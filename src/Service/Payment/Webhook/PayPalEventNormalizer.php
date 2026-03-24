<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment\Webhook;

final class PayPalEventNormalizer
{
    public function routingKey(array $payload): string
    {
        return match ((string) ($payload['event_type'] ?? '')) {
            'PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED' => 'payment.captured',
            'PAYMENT.CAPTURE.REFUNDED' => 'payment.refunded',
            'PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.DECLINED' => 'payment.failed',
            default => 'payment.webhook.received',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(array $payload): array
    {
        $resource = is_array($payload['resource'] ?? null) ? $payload['resource'] : [];
        $amount = is_array($resource['amount'] ?? null) ? $resource['amount'] : [];
        $supplementary = is_array($resource['supplementary_data'] ?? null) ? $resource['supplementary_data'] : [];
        $relatedIds = is_array($supplementary['related_ids'] ?? null) ? $supplementary['related_ids'] : [];

        return [
            'paymentId' => $this->firstNonEmpty([
                $resource['custom_id'] ?? null,
                $resource['invoice_id'] ?? null,
            ]),
            'orderId' => $this->firstNonEmpty([
                $relatedIds['order_id'] ?? null,
                $resource['invoice_id'] ?? null,
            ]),
            'amountMinor' => $this->extractAmountMinor($amount),
            'currency' => strtoupper((string) ($amount['currency_code'] ?? 'USD')),
            'gatewayTransactionId' => $this->firstNonEmpty([
                $resource['id'] ?? null,
                $payload['id'] ?? null,
            ]),
            'reason' => $this->firstNonEmpty([
                $payload['summary'] ?? null,
                $resource['status'] ?? null,
            ]),
            'errorCode' => (string) ($resource['status'] ?? ''),
            'message' => $this->firstNonEmpty([
                $payload['summary'] ?? null,
                $payload['event_type'] ?? null,
            ]),
            'externalEventId' => (string) ($payload['id'] ?? ''),
            'raw' => $payload,
        ];
    }

    /**
     * @param array<string, mixed> $amount
     */
    private function extractAmountMinor(array $amount): int
    {
        $value = $amount['value'] ?? null;
        if (!is_numeric($value)) {
            return 0;
        }

        return (int) round(((float) $value) * 100);
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
