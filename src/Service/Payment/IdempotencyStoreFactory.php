<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\Infrastructure\Payment\DbalIdempotencyStore;
use App\Infrastructure\Payment\RedisIdempotencyStore;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class IdempotencyStoreFactory
{
    public function __construct(private readonly Connection $data, private readonly LoggerInterface $logger)
    {
    }

    public function create(): IdempotencyStoreInterface
    {
        $url = (string) ($_ENV['REDIS_URL'] ?? '');
        if ('' !== $url && class_exists(\Redis::class)) {
            try {
                return new RedisIdempotencyStore($url);
            } catch (\Throwable $e) {
                $this->logger->warning('Falling back to DBAL idempotency store.', ['exception' => $e]);
            }
        }

        return new DbalIdempotencyStore($this->data);
    }
}
