<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Entity\Payment;
use App\Entity\PaymentWebhookLog;
use App\RepositoryInterface\PaymentRepositoryInterface;
use App\ServiceInterface\PaymentConsoleReadModelInterface;
use Doctrine\ORM\EntityManagerInterface;

final class PaymentConsoleReadModel implements PaymentConsoleReadModelInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function build(string $query, string $status, string $selectedPaymentId): array
    {
        $normalizedQuery = trim($query);
        $normalizedStatus = strtolower(trim($status));
        $normalizedStatus = '' === $normalizedStatus ? 'all' : $normalizedStatus;

        $paymentRows = array_map(fn (Payment $payment): array => $this->toPaymentRow($payment), $this->payments->listRecent(100));

        $filteredPayments = array_values(array_filter(
            $paymentRows,
            fn (array $payment): bool => $this->matchStatus($payment, $normalizedStatus) && $this->matchQuery($payment, $normalizedQuery)
        ));

        $selectedPayment = $this->resolveSelectedPayment($filteredPayments, $selectedPaymentId);

        return [
            'payments' => $filteredPayments,
            'selectedPayment' => $selectedPayment,
            'events' => $this->listWebhookEvents($selectedPayment['id'] ?? ''),
            'filters' => [
                'q' => $normalizedQuery,
                'status' => $normalizedStatus,
            ],
        ];
    }

    /** @param array{id: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string} $payment */
    private function matchStatus(array $payment, string $status): bool
    {
        if ('all' === $status) {
            return true;
        }

        return $payment['status'] === $status;
    }

    /** @param array{id: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string} $payment */
    private function matchQuery(array $payment, string $query): bool
    {
        if ('' === $query) {
            return true;
        }

        $candidate = strtolower(implode(' ', [
            (string) $payment['id'],
            (string) $payment['providerRef'],
            (string) $payment['currency'],
            (string) $payment['amount'],
        ]));

        return str_contains($candidate, strtolower($query));
    }

    /**
     * @param list<array{id: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}> $filteredPayments
     *
     * @return array{id: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string}|null
     */
    private function resolveSelectedPayment(array $filteredPayments, string $selectedPaymentId): ?array
    {
        $id = trim($selectedPaymentId);
        if ('' !== $id) {
            foreach ($filteredPayments as $payment) {
                if ($payment['id'] === $id) {
                    return $payment;
                }
            }
        }

        return $filteredPayments[0] ?? null;
    }

    /** @return list<array{id: string, provider: string, externalEventId: string, status: string, receivedAt: string}> */
    private function listWebhookEvents(string $paymentId): array
    {
        $logs = $this->em->getRepository(PaymentWebhookLog::class)->findBy([], ['receivedAt' => 'DESC'], 50);

        $events = [];
        foreach ($logs as $log) {
            if (!$log instanceof PaymentWebhookLog) {
                continue;
            }

            $payload = $log->payload();
            $payloadPaymentId = isset($payload['paymentId']) ? (string) $payload['paymentId'] : '';
            if ('' !== $paymentId && $payloadPaymentId !== $paymentId) {
                continue;
            }

            $events[] = [
                'id' => $log->id(),
                'provider' => $log->provider(),
                'externalEventId' => $log->externalEventId(),
                'status' => $log->status(),
                'receivedAt' => $log->receivedAt()->format(\DateTimeInterface::ATOM),
            ];

            if (20 === count($events)) {
                break;
            }
        }

        return $events;
    }

    /** @return array{id: string, status: string, amount: string, currency: string, providerRef: ?string, updatedAt: string} */
    private function toPaymentRow(Payment $payment): array
    {
        return [
            'id' => (string) $payment->id(),
            'status' => $payment->status()->value,
            'amount' => $payment->amount(),
            'currency' => $payment->currency(),
            'providerRef' => $payment->providerRef(),
            'updatedAt' => $payment->updatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
