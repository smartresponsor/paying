<?php

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\TracerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class Tracer implements TracerInterface
{
    private const ATTR_TRACE_ID = '_trace_id';
    private const ATTR_SPAN_STACK = '_span_stack';

    public function __construct(
        private RequestStack $requestStack,
        private LoggerInterface $logger,
    ) {
    }

    public function startTrace(?string $incomingTraceparent = null): string
    {
        $traceId = $this->extractTraceId($incomingTraceparent) ?? bin2hex(random_bytes(16));

        $this->set(self::ATTR_TRACE_ID, $traceId);
        $this->set(self::ATTR_SPAN_STACK, []);

        return $traceId;
    }

    public function currentTraceId(): string
    {
        return $this->get(self::ATTR_TRACE_ID) ?? $this->startTrace(null);
    }

    public function currentSpanId(): ?string
    {
        $stack = $this->get(self::ATTR_SPAN_STACK) ?? [];
        return end($stack) ?: null;
    }

    public function formatTraceparent(?string $spanId = null): string
    {
        $traceId = $this->currentTraceId();
        $spanId = $spanId ?? ($this->currentSpanId() ?? bin2hex(random_bytes(8)));

        return sprintf('00-%s-%s-01', $traceId, $spanId);
    }

    public function inSpan(string $name, array $attributes, callable $callback): mixed
    {
        $spanId = bin2hex(random_bytes(8));
        $stack = $this->get(self::ATTR_SPAN_STACK) ?? [];
        $stack[] = $spanId;
        $this->set(self::ATTR_SPAN_STACK, $stack);

        $start = microtime(true);

        try {
            return $callback();
        } finally {
            $durationMs = (microtime(true) - $start) * 1000;

            $this->logger->info('trace.span', [
                'traceId' => $this->currentTraceId(),
                'spanId' => $spanId,
                'name' => $name,
                'durationMs' => $durationMs,
                'attributes' => $attributes,
            ]);

            array_pop($stack);
            $this->set(self::ATTR_SPAN_STACK, $stack);
        }
    }

    private function extractTraceId(?string $traceparent): ?string
    {
        if (!$traceparent) {
            return null;
        }

        $parts = explode('-', $traceparent);
        return $parts[1] ?? null;
    }

    private function get(string $key): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->attributes->get($key);
    }

    private function set(string $key, mixed $value): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request) {
            $request->attributes->set($key, $value);
        }
    }
}
