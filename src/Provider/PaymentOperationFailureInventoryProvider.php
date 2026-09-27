<?php

declare(strict_types=1);

namespace App\Paying\Provider;

use App\Failing\Contract\OperationFailureInventoryProviderInterface;
use App\Failing\DTO\OperationFailureInventoryDTO;
use App\Failing\ValueObject\FailureCode;

/**
 * Declares Paying-owned failure membership for external payment operations.
 */
final class PaymentOperationFailureInventoryProvider implements OperationFailureInventoryProviderInterface
{
    public function inventories(): iterable
    {
        yield new OperationFailureInventoryDTO(
            'GET',
            '/api/payments/{id}',
            [new FailureCode(PaymentFailureProvider::PAYMENT_NOT_FOUND)],
        );
    }
}
