<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Exception;

/**
 * Signals a failure while enqueueing, publishing, or replaying payment outbox work.
 */
final class OutboxOperationException extends \RuntimeException
{
}
