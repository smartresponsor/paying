<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Payment;
use Symfony\Component\Uid\Ulid;

interface ReconciliationServiceInterface
{
    public function reconcile(Ulid $id, string $provider = 'internal'): Payment;

    /**
     * @return list<string>
     */
    public function listProcessingIds(int $limit = 100): array;
}
