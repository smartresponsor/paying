<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\HttpFoundation\Request;

interface ApiJsonBodyDecoderInterface
{
    /** @return array<string, mixed>|null */
    public function decode(Request $request, bool $allowEmptyObject = false): ?array;
}
