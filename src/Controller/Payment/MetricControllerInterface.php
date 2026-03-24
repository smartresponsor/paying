<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Controller\Payment;

use Symfony\Component\HttpFoundation\Response;

interface MetricControllerInterface
{
    public function metrics(): Response;
}
