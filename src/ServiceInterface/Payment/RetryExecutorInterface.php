<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface RetryExecutorInterface
{
    /**
     * @template T
     * @param callable():T $callable
     * @return T
     */
    public function execute(callable $callable);
}
