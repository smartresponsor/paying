<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payment;
use App\ServiceInterface\Payment\IdempotencyStoreInterface;
use App\ServiceInterface\Payment\IdempotencyServiceInterface;

use Symfony\Component\HttpFoundation\Request;

class IdempotencyService implements IdempotencyServiceInterface
{
    public function __construct(private readonly IdempotencyStoreInterface $store, private readonly int $ttlSec = 86400)
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

    /** @return array<string, mixed> */
    public function once(Request $req, callable $producer): array
    {
        return $this->execute($this->keyFor($req), hash('sha256', (string) $req->getContent()), $producer);
    }

    /** @return array<string, mixed> */
    public function execute(string $key, string $payloadHash, callable $producer): array
    {
        $cacheKey = 'payment:idem:direct:'.$key.':'.$payloadHash;
        $cached = $this->store->get($cacheKey);
        if (null !== $cached) {
            $decoded = json_decode($cached, true);

            return is_array($decoded) ? $decoded : [];
        }

        $result = $producer();
        $this->store->put($cacheKey, json_encode($result, JSON_THROW_ON_ERROR), $this->ttlSec);

        return $result;
    }
}
