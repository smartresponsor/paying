<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ControllerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the generic provider webhook endpoint for the payment runtime.
 */
interface PaymentWebhookControllerInterface
{
    /**
     * Processes a provider webhook request and returns the transport-level acknowledgment.
     */
    public function webhook(string $provider, Request $request): Response;
}
