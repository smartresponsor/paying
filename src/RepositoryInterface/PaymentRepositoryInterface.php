<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Payment;

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
