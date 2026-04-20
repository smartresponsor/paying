<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Attribute;

/**
 * Declares the authorization scopes that must be satisfied before a class or method is executed.
 *
 * This attribute lets transport or controller-level authorization middleware express scope
 * requirements next to the protected application surface.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class RequireScope
{
    /**
     * Captures the scopes that are accepted for the protected surface.
     */
    public function __construct(public array $scopes, public bool $any = false)
    {
    }
}
