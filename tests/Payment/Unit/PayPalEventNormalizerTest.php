<?php

// Marketing America Corp. Oleksandr Tishchenko
declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Service\Payment\Webhook\PayPalEventNormalizer;
use PHPUnit\Framework\TestCase;

final class PayPalEventNormalizerTest extends TestCase
{
    public function testNormalizeBuildsConsumerPayload(): void
    {
        $normalizer = new PayPalEventNormalizer();

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
