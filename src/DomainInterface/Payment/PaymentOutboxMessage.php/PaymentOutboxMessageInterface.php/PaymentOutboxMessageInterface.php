<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

are(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */
namespace App\DomainInterface\Payment;

interface PaymentOutboxMessageInterface
{
    public function __construct(string $id, string $type, array $payload, ?string $routingKey = null);
    public function id(): string;
    public function type(): string;
    public function payload(): array;
    public function routingKey(): ?string;
    public function markPublished(): void;
    public function markFailed(string $error): void;
    public function incrementAttempts(): void;
    public function status(): string;
    public function attempts(): int;
}
