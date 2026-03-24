<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;
use App\Entity\Payment;
use Symfony\Component\Uid\Ulid;

interface RefundServiceInterface
{
    public function refund(Ulid $id, string $amount, string $provider = 'internal'): Payment;
}
