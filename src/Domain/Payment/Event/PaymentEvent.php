<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Domain\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PaymentEvent extends Event
{
    public function __construct(public readonly string $name) {}
}
