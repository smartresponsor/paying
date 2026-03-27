<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface EventMapperInterface
{
    public function provider(): string;

    /** @param array<string, mixed> $payload */
    public function extractPaymentId(array $payload): ?string;

    /** @param array<string, mixed> $payload */
    public function mapStatus(array $payload): ?string;
}
