<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

/**
 * Defines the contract for the sla reporter interface payment service boundary.
 */
interface SlaReporterInterface
{
    /**
     * Returns the value exposed by the since accessor.
     */
    public function since(string $isoInterval): array;
}
