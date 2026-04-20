<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\ServiceInterface;

use App\Paying\Entity\Payment;
use Symfony\Component\Uid\Ulid;

/**
 * Defines the contract for the provider guard interface payment service boundary.
 */
interface ProviderGuardInterface
{
    /**
     * Executes the start operation for the current payment workflow.
     *
     * @param array<string, mixed> $context
     *
     * @return array{provider: string, paymentId: string, accepted?: bool, status?: string, providerRef?: string|null, checkoutUrl?: string, result?: array<string, mixed>}
     */
    public function start(string $provider, Payment $payment, array $context = []): array;

    /**
     * Executes the finalize operation for the current payment workflow.
     */
    public function finalize(string $provider, Ulid $id, array $payload = []): Payment;

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(string $provider, Ulid $id, string $amount): Payment;

    /**
     * Executes the reconcile operation for the current payment workflow.
     */
    public function reconcile(string $provider, Ulid $id): Payment;
}
