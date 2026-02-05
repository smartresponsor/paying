<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\InfrastructureInterface\Payment;

interface IdempotencyStoreInterface
{
    public function get(string $key): ?array;
    public function save(string $key, string $hash, array $response): void;
}
