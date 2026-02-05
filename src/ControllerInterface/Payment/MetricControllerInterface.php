<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ControllerInterface\Payment;

use Symfony\Component\HttpFoundation\Response;

interface MetricControllerInterface
{
    public function metrics(): Response;
}
