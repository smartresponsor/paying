<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface RefundServiceInterface
{
    public function refund(Ulid $id, string $amount, string $provider = 'internal'): Payment;
}
