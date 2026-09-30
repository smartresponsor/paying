<?php

declare(strict_types=1);

namespace App\Paying\Provider;

use App\Failing\Contract\FailureOperationInventoryProviderInterface;
use App\Failing\DTO\FailureOperationInventoryDTO;
use App\Failing\ValueObject\FailureCode;

/**
 * Declares Paying-owned failure membership for external payment operations.
 */
final class PaymentOperationFailureInventoryProvider implements FailureOperationInventoryProviderInterface
{
    public function inventories(): iterable
    {
        yield new FailureOperationInventoryDTO(
            'GET',
            '/api/payments/{id}',
            [new FailureCode(PaymentFailureProvider::PAYMENT_NOT_FOUND)],
        );
    }
}
