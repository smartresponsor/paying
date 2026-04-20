<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Exception;

/**
 * Signals a read-side failure while loading payment state from persistence.
 */
final class PaymentRepositoryReadException extends \RuntimeException
{
}
