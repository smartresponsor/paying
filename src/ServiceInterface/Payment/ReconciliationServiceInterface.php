<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

use Symfony\Component\Uid\Ulid;
use App\Entity\Payment\Payment;

interface ReconciliationServiceInterface
{
    public function reconcile(Ulid $id, string $provider = 'internal'): Payment;
    /** @return list<string> ULIDs */
    public function listProcessingIds(int $limit = 100): array;
}
