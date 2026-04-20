<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ControllerInterface;

use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the metrics endpoint exposed for payment observability.
 */
interface MetricControllerInterface
{
    /**
     * Renders the module metrics payload for scraping systems.
     */
    public function metrics(): Response;
}
