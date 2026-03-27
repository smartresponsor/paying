<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface ProjectionLagServiceInterface
{
    /**
     * @return array{updatedAtData: string, updatedAtInfra: string, projectionLagMs: int}
     */
    public function snapshot(): array;
}
