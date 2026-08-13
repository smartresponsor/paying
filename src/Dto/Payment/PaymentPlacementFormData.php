<?php

declare(strict_types=1);

namespace App\Paying\Dto\Payment;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentPlacementFormData
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(?:\.\d{1,2})?$/')]
    #[Assert\Positive]
    public string $amount = '0.00';

    #[Assert\Currency]
    public string $currency = 'USD';

    #[Assert\Choice(choices: ['internal', 'stripe', 'paypal'])]
    public string $provider = 'internal';
}
