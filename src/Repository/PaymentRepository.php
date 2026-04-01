<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Payment;
use App\Exception\PaymentRepositoryReadException;
use App\RepositoryInterface\PaymentRepositoryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function save(Payment $payment): void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    public function find(string $id): ?Payment
    {
        $payment = $this->em->find(Payment::class, $id);
        if (!$payment instanceof Payment) {
            return null;
        }

        if ($this->em->contains($payment)) {
            $this->em->refresh($payment);
        }

        return $payment;
    }

    public function findByOrderId(string $orderId): ?Payment
    {
        $payment = $this->em->getRepository(Payment::class)->findOneBy(['orderId' => $orderId]);
        if (!$payment instanceof Payment) {
            return null;
        }

        if ($this->em->contains($payment)) {
            $this->em->refresh($payment);
        }

        return $payment;
    }

    public function listRecent(int $limit = 10): array
    {
        $limit = max(1, $limit);

        $payments = $this->em->getRepository(Payment::class)->findBy([], ['updatedAt' => 'DESC'], $limit);

        return array_values(array_filter($payments, static fn (mixed $payment): bool => $payment instanceof Payment));
    }

    public function listIdsByStatuses(array $statuses, int $limit = 100): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $status): string => strtolower(trim((string) $status)),
            $statuses,
        ))));

        if ([] === $normalized || $limit < 1) {
            return [];
        }

        try {
            $rows = $this->em->getConnection()->fetchFirstColumn(
                'SELECT id FROM payment WHERE status IN (?) ORDER BY updated_at ASC LIMIT ?',
                [$normalized, $limit],
                [ArrayParameterType::STRING, ParameterType::INTEGER],
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to list payment ids by statuses.', [
                'statuses' => $normalized,
                'limit' => $limit,
                'exception' => $e,
            ]);

            throw new PaymentRepositoryReadException('Unable to list payment ids by statuses.', 0, $e);
        }

        return array_values(array_map(static fn (mixed $id): string => (string) $id, $rows));
    }
}
