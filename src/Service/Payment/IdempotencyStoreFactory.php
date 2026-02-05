<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\IdempotencyStoreInterface;
use App\Infrastructure\Payment\RedisIdempotencyStore;
use App\Infrastructure\Payment\DbalIdempotencyStore;
use Doctrine\DBAL\Connection;

class IdempotencyStoreFactory
{
    public function __construct(private readonly Connection $data) {}

    public function create(): IdempotencyStoreInterface
    {
        $url = (string)($_ENV['REDIS_URL'] ?? '');
        if ($url !== '' && class_exists(\Redis::class)) {
            try { return new RedisIdempotencyStore($url); } catch (\Throwable $e) { /* fallback to DBAL */ }
        }
        return new DbalIdempotencyStore($this->data);
    }
}
