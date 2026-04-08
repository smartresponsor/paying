<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Defines the contract for the projection sync interface payment service boundary.
 */
interface ProjectionSyncInterface
{
    /**
     * Executes the sync operation for the current payment workflow.
     */
    public function sync(int $limit = 500): int;

    /**
     * Executes the rebuild operation for the current payment workflow.
     */
    public function rebuild(int $batch = 1000): int;
}
