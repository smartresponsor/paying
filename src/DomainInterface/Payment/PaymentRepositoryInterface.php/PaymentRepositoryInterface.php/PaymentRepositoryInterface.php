<?php
namespace App\DomainInterface\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space App\DomainInterface\Payment;

declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

space OrderComponent\Payment\Contract\RepositoryInterface\Payment;

use OrderComponent\Payment\Entity\Payment\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): void;
    public function find(string $id): ?Payment;
}
