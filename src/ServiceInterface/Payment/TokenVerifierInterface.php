<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\ServiceInterface\Payment;

interface TokenVerifierInterface
{
    /** @return array<string,mixed> claims */
    public function verify(string $jwt): array;

    /** @param array<string,mixed> $claims */
    public function hasScopes(array $claims, array $required, bool $any = false): bool;
}
