<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface WebhookVerifierInterface
{
    /**
     * @param array<string, string|list<string>> $headers
     */
    public function verify(string $provider, string $raw, array $headers): bool;
}
