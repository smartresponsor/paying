<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApiJsonBodyDecoderInterface;
use Symfony\Component\HttpFoundation\Request;

final class ApiJsonBodyDecoder implements ApiJsonBodyDecoderInterface
{
    public function decode(Request $request, bool $allowEmptyObject = false): ?array
    {
        $content = $request->getContent();
        if ('' === trim($content)) {
            return $allowEmptyObject ? [] : null;
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
