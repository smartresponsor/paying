<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Unit;

use App\Paying\Service\Webhook\PaymentPayPalEventNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the pay pal event normalizer scenario within the payment unit test surface.
 */
final class PaymentPayPalEventNormalizerTest extends TestCase
{
    /**
     * Verifies that normalize builds consumer payload.
     */
    public function testNormalizeBuildsConsumerPayload(): void
    {
        $normalizer = new PaymentPayPalEventNormalizer();

        $payload = [
            'id' => 'WH-123',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'summary' => 'Capture completed',
            'resource' => [
                'id' => 'CAP-123',
                'custom_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
                'amount' => [
                    'value' => '10.99',
                    'currency_code' => 'usd',
                ],
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'ord_321',
                    ],
                ],
            ],
        ];

        $normalized = $normalizer->normalize($payload);

        self::assertSame('payment.captured', $normalizer->routingKey($payload));
        self::assertSame('01ARZ3NDEKTSV4RRFFQ69G5FAV', $normalized['paymentId']);
        self::assertSame('ord_321', $normalized['orderId']);
        self::assertSame(1099, $normalized['amountMinor']);
        self::assertSame('USD', $normalized['currency']);
        self::assertSame('CAP-123', $normalized['gatewayTransactionId']);
        self::assertSame('WH-123', $normalized['externalEventId']);
    }
}
