<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface ProjectionSyncInterface
{
    public function sync(int $limit = 500): int;
    public function rebuild(int $batch = 1000): int;
}
