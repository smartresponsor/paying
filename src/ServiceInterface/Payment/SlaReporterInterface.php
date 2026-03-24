<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;

interface SlaReporterInterface
{
    public function since(string $isoInterval): array;
}
