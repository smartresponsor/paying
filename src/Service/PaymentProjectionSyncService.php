<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\PaymentProjectionSyncServiceInterface;

/**
 * Provides the projection sync service used by the payment lifecycle and operator-facing flows.
 */
readonly class PaymentProjectionSyncService implements PaymentProjectionSyncServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private PaymentProjectionRepositoryInterface $paymentProjectionRepository,
    ) {
    }

    /**
     * Executes the sync operation for the current payment workflow.
     */
    public function sync(int $limit = 500): int
    {
        $wm = $this->paymentProjectionRepository->watermark();
        $rows = $this->payments->listUpdatedAfter(
            new \DateTimeImmutable($wm ?? '1970-01-01 00:00:00'),
            $limit,
        );

        $n = 0;
        $lastUpdatedAt = null;
        foreach ($rows as $r) {
            $this->paymentProjectionRepository->upsert([
                'id' => $r->slug(),
                'order_id' => (string) $r->orderId(),
                'amount' => (string) $r->amount(),
                'currency' => (string) $r->currency(),
                'status' => (string) $r->status()->value,
                'provider_ref' => $r->providerRef(),
                'updated_at' => $r->updatedAt()->format('Y-m-d H:i:s'),
            ]);
            $lastUpdatedAt = $r->updatedAt()->format('Y-m-d H:i:s');
            ++$n;
        }

        if (null !== $lastUpdatedAt) {
            $this->paymentProjectionRepository->saveWatermark($lastUpdatedAt);
        }

        return $n;
    }

    /**
     * Executes the rebuild operation for the current payment workflow.
     */
    public function rebuild(int $batch = 1000): int
    {
        $off = 0;
        $n = 0;
        $lastUpdatedAt = null;
        while (true) {
            $rows = $this->payments->listAllOrderedByUpdatedAt($batch, $off);
            if (!$rows) {
                break;
            }
            foreach ($rows as $r) {
                $this->paymentProjectionRepository->upsert([
                    'id' => $r->slug(),
                    'order_id' => (string) $r->orderId(),
                    'amount' => (string) $r->amount(),
                    'currency' => (string) $r->currency(),
                    'status' => (string) $r->status()->value,
                    'provider_ref' => $r->providerRef(),
                    'updated_at' => $r->updatedAt()->format('Y-m-d H:i:s'),
                ]);
                $lastUpdatedAt = $r->updatedAt()->format('Y-m-d H:i:s');
                ++$n;
            }
            $off += $batch;
            $this->paymentProjectionRepository->saveWatermark($lastUpdatedAt);
        }

        return $n;
    }
}
