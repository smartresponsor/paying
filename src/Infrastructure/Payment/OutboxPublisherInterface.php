<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Infrastructure\Payment;

interface OutboxPublisherInterface
{
    public function enqueue(string $topic, array $payload): void;

    public function moveToDlq(string $id, string $reason): void;
}
