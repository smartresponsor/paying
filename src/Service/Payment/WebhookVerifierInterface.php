<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;

interface WebhookVerifierInterface
{
    /**
     * @param array<string, string|list<string>> $headers
     */
    public function verify(string $provider, string $raw, array $headers): bool;
}
