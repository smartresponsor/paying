<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\IdempotencyServiceInterface;
use App\ServiceInterface\IdempotencyStoreInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class IdempotencyService implements IdempotencyServiceInterface
{
    public function __construct(private IdempotencyStoreInterface $store, private int $ttlSec = 86400)
    {
    }

    public function keyFor(Request $req): string
    {
        $h = (string) $req->headers->get('Idempotency-Key', '');
        $path = (string) $req->getPathInfo();
        $body = (string) $req->getContent();
        $hash = hash('sha256', $path.'|'.$h.'|'.$body);

        return 'payment:idem:api:'.$hash;
    }

    /**
     * @template T of array<string, mixed>
     *
     * @param callable(): T $producer
     *
     * @return T
     *
     * @throws \JsonException
     */
    public function once(Request $req, callable $producer): array
    {
        return $this->execute($this->keyFor($req), hash('sha256', (string) $req->getContent()), $producer);
    }

    /**
     * @template T of array<string, mixed>
     *
     * @param callable(): T $producer
     *
     * @return T
     *
     * @throws \JsonException
     */
    public function execute(string $key, string $payloadHash, callable $producer): array
    {
        $cacheKey = 'payment:idem:direct:'.$key.':'.$payloadHash;
        $cached = $this->store->get($cacheKey);
        if (null !== $cached) {
            /** @var T $decoded */
            $decoded = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        }

        /** @var T $result */
        $result = $producer();
        $this->store->put($cacheKey, json_encode($result, JSON_THROW_ON_ERROR), $this->ttlSec);

        return $result;
    }
}
