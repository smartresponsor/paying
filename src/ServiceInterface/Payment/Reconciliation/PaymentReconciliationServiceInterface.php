<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface PaymentReconciliationServiceInterface
{
    public function __construct(private PaymentRepositoryInterface $payments, private EntityManagerInterface $em);
    public function onCaptured(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null);
    public function onRefunded(string $paymentId, int $amountMinor, string $currency, ?string $gatewayTxId = null, ?string $reason = null);
    public function onFailed(string $paymentId, string $errorCode, ?string $message = null);
}
