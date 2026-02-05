<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Domain\Payment;

enum PaymentStatus: string
{
    case new = 'new';
    case processing = 'processing';
    case completed = 'completed';
    case failed = 'failed';
    case canceled = 'canceled';
    case refunded = 'refunded';
}
