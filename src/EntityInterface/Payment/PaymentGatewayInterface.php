<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\EntityInterface\Payment;

interface PaymentGatewayInterface
{
    public function __construct(string $id, string $code);
}
