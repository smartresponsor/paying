<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use Symfony\Component\Uid\Ulid;

interface ReconciliationServiceInterface
{
    public function reconcile(Ulid $id, string $provider = 'internal'): Payment;

    /**
     * @return list<string>
     */
    public function listProcessingIds(int $limit = 100): array;
}
