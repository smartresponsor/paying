<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface IdempotencyServiceInterface
{
    public function execute(string $key, string $hash, callable $producer): array;
}
