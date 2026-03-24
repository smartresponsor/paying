<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;
use App\ServiceInterface\Payment\RetryExecutorInterface;

class RetryExecutor implements RetryExecutorInterface
{
    public function __construct(private readonly int $max = 3, private readonly int $baseMs = 50)
    {
    }

    public function execute(callable $callable): mixed
    {
        $attempt = 0;
        $sleep = $this->baseMs;
        beginning:
        try {
            return $callable();
        } catch (\Throwable $e) {
            if ($attempt >= $this->max) {
                throw $e;
            }
            usleep($sleep * 1000);
            $sleep *= 2;
            ++$attempt;
            goto beginning;
        }
    }
}
