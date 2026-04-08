<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the retry executor interface payment service boundary.
 */
interface RetryExecutorInterface
{
    /**
     * Executes the execute operation for the current payment workflow.
     */
    public function execute(callable $callable): mixed;
}
