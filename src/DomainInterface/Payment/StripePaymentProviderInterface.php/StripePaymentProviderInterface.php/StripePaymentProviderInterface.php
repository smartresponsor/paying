<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface StripePaymentProviderInterface
{
    public function __construct(?string $secretKey = null, ?string $webhookSecret = null);
    public function create(string $projectId, float $amount, string $currency, string $idempotencyKey);
    public function verifyWebhook(string $rawBody, string $signatureHeader);
}
