<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Payment\Outbox;

interface PaymentOutboxProcessorInterface
{
    public function process(int $limit = 50, bool $retryFailed = false): int;
}
