<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Repository\Payment;

use App\Entity\Payment\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): void;

    public function find(string $id): ?Payment;

    /**
     * @return list<Payment>
     */
    public function listRecent(int $limit = 10): array;

    /**
     * @param list<string> $statuses
     *
     * @return list<string>
     */
    public function listIdsByStatuses(array $statuses, int $limit = 100): array;
}
