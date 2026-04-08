<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\PaymentFinalizePayload;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the payment finalize payload scenario within the payment valueobject test surface.
 */
final class PaymentFinalizePayloadTest extends TestCase
{
    /**
     * Verifies that to provider payload filters empty values.
     */
    public function testToProviderPayloadFiltersEmptyValues(): void
    {
        $payload = new PaymentFinalizePayload('ref-1', '', 'completed');

        self::assertSame([
            'providerRef' => 'ref-1',
            'status' => 'completed',
        ], $payload->toProviderPayload());
    }
}
