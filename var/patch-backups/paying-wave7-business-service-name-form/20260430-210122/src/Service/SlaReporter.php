<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\RepositoryInterface\PaymentRepositoryInterface;
use App\Paying\ServiceInterface\SlaReporterInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides the sla reporter service used by the payment lifecycle and operator-facing flows.
 */
readonly class SlaReporter implements SlaReporterInterface
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * Returns the value exposed by the since accessor.
     */
    public function since(string $isoInterval): array
    {
        $interval = trim($isoInterval);
        $window = '' !== $interval ? $interval : 'P1D';
        $map = ['completed' => 0, 'failed' => 0, 'canceled' => 0, 'refunded' => 0];

        try {
            $since = (new \DateTimeImmutable('now'))->sub(new \DateInterval($window));
            $rows = $this->payments->countByStatusSince($since);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to read payment SLA report rows.', ['exception' => $e, 'window' => $window]);
            $rows = [];
        }

        $total = 0;
        foreach ($rows as $status => $count) {
            $status = (string) $status;
            $count = (int) $count;
            $total += $count;
            if (isset($map[$status])) {
                $map[$status] = $count;
            }
        }

        $completed = $map['completed'];
        $failed = $map['failed'];
        $canceled = $map['canceled'];
        $refunded = $map['refunded'];
        $success = $total > 0 ? ($completed / $total) * 100.0 : 100.0;

        return [
            'window' => $window,
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'canceled' => $canceled,
            'refunded' => $refunded,
            'successRate' => $success,
        ];
    }
}
