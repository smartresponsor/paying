<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface SlaReporterInterface
{
    public function since(string $isoInterval): array;
}
