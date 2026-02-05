<?php
namespace OrderComponent\Payment\ValueObject\Payment;

final class PaymentReference
{
    public function __construct(private string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('PaymentReference cannot be empty');
        }
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
