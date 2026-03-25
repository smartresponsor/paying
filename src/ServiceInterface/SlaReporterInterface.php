<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;

interface SlaReporterInterface
{
    /** @return array{window: string, total: int, completed: int, failed: int, canceled: int, refunded: int, successRate: float} */
    public function since(string $isoInterval): array;
}
