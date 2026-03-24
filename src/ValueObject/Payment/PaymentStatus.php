<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ValueObject\Payment;

enum PaymentStatus: string
{
    case new = 'new';
    case pending = 'pending';
    case processing = 'processing';
    case completed = 'completed';
    case failed = 'failed';
    case canceled = 'canceled';
    case refunded = 'refunded';
}
