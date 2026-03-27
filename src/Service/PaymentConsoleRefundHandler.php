<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\ServiceInterface\PaymentConsoleRefundHandlerInterface;
use App\ServiceInterface\RefundServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Ulid;

final class PaymentConsoleRefundHandler implements PaymentConsoleRefundHandlerInterface
{
    public function __construct(
        private readonly RefundServiceInterface $refundService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function refund(string $paymentId, string $amount, string $provider): ?Payment
    {
        try {
            return $this->refundService->refund(new Ulid($paymentId), $amount, $provider);
        } catch (PaymentNotFoundException $exception) {
            $this->logger->warning('Payment console refund failed.', [
                'payment_id' => $paymentId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
