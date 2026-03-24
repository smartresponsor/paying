<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface TokenVerifierInterface
{
    public function verify(string $jwt): array;

    public function hasScopes(array $claims, array $required, bool $any = false): bool;
}
