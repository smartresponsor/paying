<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Infrastructure\PaymentDbalIdempotencyStore;
use App\Paying\Infrastructure\PaymentRedisIdempotencyStore;
use App\Paying\ServiceInterface\IdempotencyStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the idempotency store factory service used by the payment lifecycle and operator-facing flows.
 */
readonly class IdempotencyStoreFactory
{
    public function __construct(
        private EntityManagerInterface $data,
        private LoggerInterface $logger,
        private string $redisUrl = '',
    ) {
    }

    /**
     * Provides the create behavior for the idempotency store factory component.
     */
    public function create(): IdempotencyStoreInterface
    {
        $url = trim($this->redisUrl);
        if ('' !== $url && class_exists(\Redis::class)) {
            try {
                return new PaymentRedisIdempotencyStore($url);
            } catch (\Throwable $e) {
                $this->logger->warning('Falling back to Doctrine idempotency store.', ['exception' => $e]);
            }
        }

        return new PaymentDbalIdempotencyStore($this->data);
    }
}
