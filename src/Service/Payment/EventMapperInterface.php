<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface EventMapperInterface
{
    public function provider(): string;

    public function extractPaymentId(array $payload): ?string;

    public function mapStatus(array $payload): ?string;
}
