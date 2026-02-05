<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface EventMapperInterface
{
    public function provider(): string;
    public function extractPaymentId(array $payload): ?string;
    public function mapStatus(array $payload): ?string;
}
