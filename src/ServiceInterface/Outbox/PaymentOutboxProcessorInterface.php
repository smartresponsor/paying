<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Outbox;

/**
 * Defines the contract for the payment outbox processor interface payment service boundary.
 */
interface PaymentOutboxProcessorInterface
{
    /**
     * Provides the process behavior for the payment outbox processor interface component.
     */
    public function process(int $limit = 50, bool $retryFailed = false): int;
}
