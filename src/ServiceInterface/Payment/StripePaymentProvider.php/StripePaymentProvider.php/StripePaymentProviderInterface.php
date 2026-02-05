<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface StripePaymentProviderInterface
{
    public function __construct(?string $secretKey = null, ?string $webhookSecret = null);
    public function create(string $projectId, float $amount, string $currency, string $idempotencyKey): array;
    public function verifyWebhook(string $rawBody, string $signatureHeader): array;
}
