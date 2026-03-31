<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\RetryExecutorInterface;

readonly class RetryExecutor implements RetryExecutorInterface
{
    public function __construct(private int $max = 3, private int $baseMs = 50)
    {
    }

    /**
     * @throws \Throwable
     */
    /**
     * @throws \Throwable
     */
    public function execute(callable $callable): mixed
    {
        $attempt = 0;
        $sleep = $this->baseMs;

        while (true) {
            try {
                return $callable();
            } catch (\Throwable $e) {
                if ($attempt >= $this->max) {
                    throw $e;
                }

                usleep($sleep * 1000);
                $sleep *= 2;
                ++$attempt;
            }
        }
    }
}
