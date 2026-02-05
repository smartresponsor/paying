<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Controller\Payment;

use App\ControllerInterface\Payment\DlqControllerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Payment\RequireScope;

final class DlqController implements DlqControllerInterface
{
    public function __construct(private readonly Connection $data) {}

    #[Route(path: '/payment/dlq', name: 'payment_dlq_list', methods: ['GET'])]
    #[RequireScope(['payment:admin'])]
    public function list(): JsonResponse
    {
        $rows = $this->data->fetchAllAssociative('SELECT id, outbox_id, topic, reason, created_at FROM payment_dlq ORDER BY id DESC LIMIT 200');
        return new JsonResponse(['items' => $rows], 200);
    }

    #[Route(path: '/payment/dlq/replay/{id}', name: 'payment_dlq_replay', methods: ['POST'])]
    #[RequireScope(['payment:admin'])]
    public function replay(int $id): JsonResponse
    {
        $row = $this->data->fetchAssociative('SELECT * FROM payment_dlq WHERE id = :id', ['id'=>$id]);
        if (!$row) return new JsonResponse(['ok'=>false], 404);
        $this->data->insert('payment_outbox', [
            'topic' => $row['topic'],
            'payload' => $row['payload'],
            'status' => 'pending',
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
        $this->data->executeStatement('DELETE FROM payment_dlq WHERE id = :id', ['id'=>$id]);
        return new JsonResponse(['ok'=>true], 200);
    }
}
