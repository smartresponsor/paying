<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\InfrastructureInterface\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface PaymentRepositoryInterface
{
    public function findById(Ulid $id): ?Payment;
    public function save(Payment $payment, bool $flush = true): void;
    /** @return list<string> ULIDs */
    public function listIdsByStatuses(array $statuses, int $limit = 100): array;
}
