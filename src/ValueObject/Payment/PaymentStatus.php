<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

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
