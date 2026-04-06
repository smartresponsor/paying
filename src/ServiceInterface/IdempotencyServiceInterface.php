<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\HttpFoundation\Request;

interface IdempotencyServiceInterface
{
    public function keyFor(Request $req): string;

    /**
     * @template T of array<string, mixed>
     *
     * @param callable(): T $producer
     *
     * @return T
     */
    public function once(Request $req, callable $producer): array;

    /**
     * @template T of array<string, mixed>
     *
     * @param callable(): T $producer
     *
     * @return T
     */
    public function execute(string $key, string $payloadHash, callable $producer): array;
}
