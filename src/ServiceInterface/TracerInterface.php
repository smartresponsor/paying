<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface TracerInterface
{
    public function startTrace(?string $incomingTraceparent = null): string;

    public function currentTraceId(): string;

    public function currentSpanId(): ?string;

    public function formatTraceparent(?string $spanId = null): string;

    /**
     * @param array<string, scalar|bool|null> $attributes
     */
    public function inSpan(string $name, array $attributes, callable $callback): mixed;
}
