<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\InfrastructureInterface\Payment;

interface OutboxPublisherInterface
{
    public function enqueue(string $topic, array $payload): void;
    public function moveToDlq(int $id, string $reason): void;
}
