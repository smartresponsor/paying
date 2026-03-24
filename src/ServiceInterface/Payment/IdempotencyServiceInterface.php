<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;

use Symfony\Component\HttpFoundation\Request;

interface IdempotencyServiceInterface
{
    public function keyFor(Request $req): string;

    /** @return array<string, mixed> */
    public function once(Request $req, callable $producer): array;

    /** @return array<string, mixed> */
    public function execute(string $key, string $payloadHash, callable $producer): array;
}
