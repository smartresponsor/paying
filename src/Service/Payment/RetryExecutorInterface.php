<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

interface RetryExecutorInterface
{
    public function execute(callable $callable): mixed;
}
