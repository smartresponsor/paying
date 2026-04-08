<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the contract for the idempotency service interface payment service boundary.
 */
interface IdempotencyServiceInterface
{
    /**
     * Provides the key for behavior for the idempotency service interface component.
     */
    public function keyFor(Request $req): string;

    /**
     * Executes the once operation for the current payment workflow.
     *
     * @template T of array<string, mixed>
     * @param callable(): T $producer
     * @return T
     */
    public function once(Request $req, callable $producer): array;

    /**
     * Executes the execute operation for the current payment workflow.
     *
     * @template T of array<string, mixed>
     * @param callable(): T $producer
     * @return T
     */
    public function execute(string $key, string $payloadHash, callable $producer): array;
}
