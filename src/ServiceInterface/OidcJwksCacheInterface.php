<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface OidcJwksCacheInterface
{
    /** @return array{keys: list<array{n: string, e: string, kty?: string, kid?: string}>} */
    public function get(): array;
}
