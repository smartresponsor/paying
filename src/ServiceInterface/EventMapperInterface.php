<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the event mapper interface payment service boundary.
 */
interface EventMapperInterface
{
    /**
     * Returns the value exposed by the provider accessor.
     */
    public function provider(): string;

    /**
     * Provides the extract payment id behavior for the event mapper interface component.
     */
    public function extractPaymentId(array $payload): ?string;

    /**
     * Provides the map status behavior for the event mapper interface component.
     */
    public function mapStatus(array $payload): ?string;
}
