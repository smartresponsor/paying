<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\EntityInterface\Payment;

use App\Domain\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

interface PaymentInterface
{
    public function id(): Ulid;
    public function status(): PaymentStatus;
    public function amount(): string;
    public function currency(): string;
    public function providerRef(): ?string;
    public function createdAt(): \DateTimeImmutable;
    public function updatedAt(): \DateTimeImmutable;
}
