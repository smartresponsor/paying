<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the projection lag service interface payment service boundary.
 */
interface ProjectionLagServiceInterface
{
    /**
     * Executes the snapshot operation for the current payment workflow.
     *
     * @return array{updatedAtData: string, updatedAtInfra: string, projectionLagMs: int}
     */
    public function snapshot(): array;
}
