<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\EntityInterface\Payment;

interface PaymentOutboxMessageInterface
{
    public function __construct(string $id, string $type, array $payload, ?string $routingKey = null);
    public function id();
    public function type();
    public function payload();
    public function routingKey();
    public function markPublished();
    public function markFailed(string $error);
    public function incrementAttempts();
    public function status();
    public function attempts();
}
