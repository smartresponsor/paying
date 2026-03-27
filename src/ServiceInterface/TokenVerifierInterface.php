<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface TokenVerifierInterface
{
    /** @return array<string, mixed> */
    public function verify(string $jwt): array;

    /**
     * @param array<string, mixed> $claims
     * @param list<string>         $required
     */
    public function hasScopes(array $claims, array $required, bool $any = false): bool;
}
