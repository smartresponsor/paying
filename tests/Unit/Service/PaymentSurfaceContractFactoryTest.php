<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit\Service;

use App\Paying\Service\Payment\PaymentSurfaceContractFactory;
use PHPUnit\Framework\TestCase;

final class PaymentSurfaceContractFactoryTest extends TestCase
{
    public function testCreateUsesCanonicalPaymentTemplatePath(): void
    {
        $factory = new PaymentSurfaceContractFactory();
        $surface = $factory->create([
            'payments' => [
                [
                    'id' => 'pay_1',
                    'orderId' => 'ord_1',
                    'status' => 'completed',
                    'amount' => '100',
                    'currency' => 'USD',
                    'providerRef' => 'stripe_pi_1',
                ],
            ],
            'selectedPayment' => [
                'id' => 'pay_1',
                'orderId' => 'ord_1',
                'status' => 'completed',
                'amount' => '100',
                'currency' => 'USD',
                'providerRef' => 'stripe_pi_1',
                'updatedAt' => '2026-05-28T10:00:00+00:00',
            ],
            'events' => [
                [
                    'provider' => 'stripe',
                    'externalEventId' => 'evt_1',
                    'status' => 'received',
                    'receivedAt' => '2026-05-28T10:01:00+00:00',
                ],
            ],
            'filters' => [
                'q' => 'pay_1',
                'status' => 'completed',
            ],
        ], 'pay_1', 'completed', 'pay_1');

        self::assertSame('payment', $surface->word);
        self::assertSame('console', $surface->view);
        self::assertSame('payment/base.html.twig', $surface->templateName());
        self::assertArrayHasKey('top.search', $surface->slotMap);
        self::assertArrayHasKey('main.body', $surface->toTemplateContext()['slots']);
        self::assertArrayHasKey('right.panel', $surface->toTemplateContext()['slots']);
    }
}
