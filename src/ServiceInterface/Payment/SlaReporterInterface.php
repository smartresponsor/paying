<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface SlaReporterInterface
{
    /** @return array{window:string,total:int,completed:int,failed:int,canceled:int,refunded:int,successRate:float} */
    public function since(string $isoInterval): array;
}
