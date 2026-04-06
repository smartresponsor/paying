<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface DlqServiceInterface
{
    /**
     * @return list<array{id: int, outbox_id: string, topic: string, reason: string, created_at: string}>
     */
    public function list(): array;

    public function replay(int $id): bool;
}
