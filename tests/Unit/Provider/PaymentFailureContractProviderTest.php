<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit\Provider;

use App\Failing\Inventory\OperationFailureInventory;
use App\Failing\Registry\FailureRegistry;
use App\Paying\Provider\PaymentFailureProvider;
use App\Paying\Provider\PaymentOperationFailureInventoryProvider;
use PHPUnit\Framework\TestCase;

final class PaymentFailureContractProviderTest extends TestCase
{
    public function testPaymentNotFoundProducesDeterministicOperationEvidence(): void
    {
        $registry = new FailureRegistry([new PaymentFailureProvider()]);
        $inventory = new OperationFailureInventory(
            [new PaymentOperationFailureInventoryProvider()],
            $registry,
        );

        self::assertSame([[
            'method' => 'GET',
            'path' => '/api/payments/{id}',
            'code' => PaymentFailureProvider::PAYMENT_NOT_FOUND,
            'status' => 404,
        ]], $inventory->evidence());
    }
}
