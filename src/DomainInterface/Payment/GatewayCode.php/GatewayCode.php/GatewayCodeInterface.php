<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\DomainInterface\Payment;

interface GatewayCodeInterface
{
    public function __construct(private string $value);
    public function value(): string;
    public function __toString(): string;
}
