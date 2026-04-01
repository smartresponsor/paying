<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface EventMapperInterface
{
    public function provider(): string;

    public function extractPaymentId(array $payload): ?string;

    public function mapStatus(array $payload): ?string;
}
