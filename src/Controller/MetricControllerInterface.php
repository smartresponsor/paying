<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

interface MetricControllerInterface
{
    public function metrics(): Response;
}
