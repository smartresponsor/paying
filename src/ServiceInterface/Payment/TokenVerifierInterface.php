<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment;

interface TokenVerifierInterface
{
    public function verify(string $jwt): array;

    public function hasScopes(array $claims, array $required, bool $any = false): bool;
}
