<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function save(Payment $payment): void
    {
        $this->em->persist($payment);
        $this->em->flush();
    }

    public function find(string $id): ?Payment
    {
        return $this->em->getRepository(Payment::class)->find($id);
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

        $rows = $this->em->getConnection()->fetchFirstColumn(
            'SELECT id FROM payment WHERE status IN (?) ORDER BY updated_at ASC LIMIT ?',
            [$normalized, $limit],
            [ArrayParameterType::STRING, ParameterType::INTEGER],
        );

        return array_values(array_map(static fn (mixed $id): string => (string) $id, $rows));
    }
}
