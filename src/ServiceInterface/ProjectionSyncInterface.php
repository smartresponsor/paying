<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;

interface ProjectionSyncInterface
{
    public function sync(int $limit = 500): int;

    public function rebuild(int $batch = 1000): int;
}
