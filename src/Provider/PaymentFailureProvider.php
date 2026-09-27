<?php

declare(strict_types=1);

namespace App\Paying\Provider;

use App\Failing\Contract\FailureProviderInterface;
use App\Failing\DTO\FailureDefinitionDTO;
use App\Failing\ValueObject\FailureCode;
use App\Failing\ValueObject\FailureType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Declares Paying-owned public failure vocabulary without implementing shared failure mechanics.
 */
final class PaymentFailureProvider implements FailureProviderInterface
{
    public const string PAYMENT_NOT_FOUND = 'payment.payment_not_found';

    public function definitions(): iterable
    {
        yield new FailureDefinitionDTO(
            new FailureCode(self::PAYMENT_NOT_FOUND),
            new FailureType('urn:paying:problem:payment-not-found'),
            Response::HTTP_NOT_FOUND,
            'Payment not found',
        );
    }
}
