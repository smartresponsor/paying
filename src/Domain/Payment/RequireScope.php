<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Domain\Payment;

use App\DomainInterface\Payment\RequireScopeInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
final class RequireScope implements RequireScopeInterface
{
    /** @param list<string> $scopes */
    public function __construct(public array $scopes, public bool $any = false) {}
}
