<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

interface OidcJwksCacheInterface
{
    /** @return array{keys?: list<array<string, mixed>>} */
    public function get(): array;
}
