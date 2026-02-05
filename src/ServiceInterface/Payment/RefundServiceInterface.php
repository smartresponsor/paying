<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

use Symfony\Component\Uid\Ulid;
use App\Entity\Payment\Payment;

interface RefundServiceInterface
{
    public function refund(Ulid $id, string $amount, string $provider = 'internal'): Payment;
}
