<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\DomainInterface\Payment;

use App\Entity\Payment\Payment;

interface TransitionHandlerInterface
{
    public function apply(Payment $payment, string $transition): bool;
}
