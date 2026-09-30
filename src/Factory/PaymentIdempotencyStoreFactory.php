<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Factory;

use App\Paying\Service\PaymentDbalIdempotencyStore;
use App\Paying\Service\PaymentRedisIdempotencyStore;
use App\Paying\ServiceInterface\PaymentIdempotencyStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the idempotency store factory service used by the payment lifecycle and operator-facing flows.
 */
readonly class PaymentIdempotencyStoreFactory
{
    public function __construct(
        private PaymentDbalIdempotencyStore $dbalStore,
        private LoggerInterface $logger,
        private string $redisUrl = '',
    ) {
    }

    /**
     * Provides the create behavior for the idempotency store factory component.
     */
    public function create(): PaymentIdempotencyStoreInterface
    {
        $url = trim($this->redisUrl);
        if ('' !== $url && class_exists(\Redis::class)) {
            try {
                return new PaymentRedisIdempotencyStore($url);
            } catch (\Throwable $e) {
                $this->logger->warning('Falling back to Doctrine idempotency store.', ['exception' => $e]);
            }
        }

        return $this->dbalStore;
    }
}
