<?php

declare(strict_types=1);

namespace App\Paying\Service\Payment;

use App\Paying\Value\Surface\PaymentSurfaceContract;

final class PaymentSurfaceContractFactory
{
    /**
     * @param array<string, mixed> $consoleView
     */
    public function create(array $consoleView, string $query, string $status, string $selectedPaymentId): PaymentSurfaceContract
    {
        $payments = $this->normalizeRows($consoleView['payments'] ?? []);
        $selectedPayment = is_array($consoleView['selectedPayment'] ?? null) ? $this->normalizeRow($consoleView['selectedPayment']) : null;
        $webhookEvents = $this->normalizeRows($consoleView['events'] ?? []);
        $filters = is_array($consoleView['filters'] ?? null) ? $consoleView['filters'] : [];

        return new PaymentSurfaceContract(
            PaymentSurfaceContract::WORD,
            PaymentSurfaceContract::VIEW_CONSOLE,
            $this->buildTemplateName('payment', 'base'),
            $this->slotMap(),
            $query,
            $status,
            $selectedPaymentId,
            $payments,
            $selectedPayment,
            $webhookEvents,
            $filters,
            [
                'top.search' => [
                    'action' => '/payment/',
                    'method' => 'GET',
                    'queryName' => 'q',
                    'placeholder' => 'PaymentEntity ID / order ID / provider ref / currency / amount',
                    'query' => $query,
                    'status' => $status,
                    'payment' => $selectedPaymentId,
                ],
                'left.panel' => [
                    'filters' => $filters,
                ],
                'main.body' => [
                    'payments' => $payments,
                    'events' => $webhookEvents,
                ],
                'right.panel' => [
                    'selectedPayment' => $selectedPayment,
                ],
            ],
        );
    }

    /**
     * @param mixed $rows
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $normalized[] = $this->normalizeRow($row);
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'id' => is_scalar($row['id'] ?? null) ? (string) $row['id'] : '',
            'orderId' => is_scalar($row['orderId'] ?? null) ? (string) $row['orderId'] : '',
            'status' => is_scalar($row['status'] ?? null) ? (string) $row['status'] : '',
            'amount' => is_scalar($row['amount'] ?? null) ? (string) $row['amount'] : '',
            'currency' => is_scalar($row['currency'] ?? null) ? (string) $row['currency'] : '',
            'providerRef' => is_scalar($row['providerRef'] ?? null) ? (string) $row['providerRef'] : '',
            'provider' => is_scalar($row['provider'] ?? null) ? (string) $row['provider'] : '',
            'providerTransactionId' => is_scalar($row['providerTransactionId'] ?? null) ? (string) $row['providerTransactionId'] : '',
            'updatedAt' => is_scalar($row['updatedAt'] ?? null) ? (string) $row['updatedAt'] : '',
            'receivedAt' => is_scalar($row['receivedAt'] ?? null) ? (string) $row['receivedAt'] : '',
            'externalEventId' => is_scalar($row['externalEventId'] ?? null) ? (string) $row['externalEventId'] : '',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function slotMap(): array
    {
        return [
            'top.search' => 'Search',
            'left.panel' => 'Filters',
            'main.body' => 'Console',
            'right.panel' => 'Selected payment',
        ];
    }

    private function buildTemplateName(string $surface, string $template): string
    {
        return sprintf('%s/%s.%s', $surface, $template, implode('.', ['html', 'twig']));
    }
}
