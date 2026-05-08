<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentEntity;
use App\Paying\ServiceInterface\PaymentConsoleRefundHandlerInterface;
use App\Paying\ServiceInterface\PaymentRefundServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

/**
 * Provides the payment console refund handler service used by the payment lifecycle and operator-facing flows.
 */
final readonly class PaymentConsoleRefundHandler implements PaymentConsoleRefundHandlerInterface
{
    public function __construct(
        private PaymentRefundServiceInterface $refundService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $paymentId, string $amount, string $provider): ?PaymentEntity
    {
        try {
            return $this->refundService->refund(new Ulid($paymentId), $amount, $provider);
        } catch (PaymentNotFoundException $exception) {
            $this->logger->warning('PaymentEntity console refund failed.', [
                'payment_id' => $paymentId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
