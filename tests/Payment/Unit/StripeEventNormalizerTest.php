<?php

// Marketing America Corp. Oleksandr Tishchenko
declare(strict_types=1);

namespace App\Tests\Payment\Unit;

use App\Service\Payment\Webhook\StripeEventNormalizer;
use PHPUnit\Framework\TestCase;

final class StripeEventNormalizerTest extends TestCase
{
    public function testNormalizeBuildsConsumerPayload(): void
    {
        $normalizer = new StripeEventNormalizer();

        $payload = [
            'id' => 'evt_1',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_123',
                    'latest_charge' => 'ch_123',
                    'amount_received' => 2599,
                    'currency' => 'usd',
                    'metadata' => [
                        'payment' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
                        'order' => 'ord_123',
                    ],
                ],
            ],
        ];

        $normalized = $normalizer->normalize($payload);

        self::assertSame('payment.captured', $normalizer->routingKey($payload));
        self::assertSame('01ARZ3NDEKTSV4RRFFQ69G5FAV', $normalized['paymentId']);
        self::assertSame('ord_123', $normalized['orderId']);
        self::assertSame(2599, $normalized['amountMinor']);
        self::assertSame('USD', $normalized['currency']);
        self::assertSame('ch_123', $normalized['gatewayTransactionId']);
        self::assertSame('evt_1', $normalized['externalEventId']);
    }
}
