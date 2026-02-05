<?php
namespace OrderComponent\Payment\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class PaymentRefundInput
{
    #[Assert\NotBlank] public string $paymentId;
    #[Assert\Positive] public int $amountMinor;
    #[Assert\Currency] public string $currency;
    public ?string $reason = null;
    public ?string $idempotencyKey = null;
}