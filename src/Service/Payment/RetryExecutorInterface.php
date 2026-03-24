<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

interface RetryExecutorInterface
{
    public function execute(callable $callable): mixed;
}
