<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the contract for the api json body decoder interface payment service boundary.
 */
interface ApiJsonBodyDecoderInterface
{
    /**
     * Decodes the incoming payload needed by the decode workflow.
     */
    public function decode(Request $request, bool $allowEmptyObject = false): ?array;
}
