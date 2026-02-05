<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\IdempotencyStoreInterface;
use Symfony\Component\HttpFoundation\Request;

class IdempotencyService
{
    public function __construct(private readonly IdempotencyStoreInterface $store, private readonly int $ttlSec = 86400) {}

    public function keyFor(Request $req): string
    {
        $h = (string)$req->headers->get('Idempotency-Key', '');
        $path = (string)$req->getPathInfo();
        $body = (string)$req->getContent();
        $hash = hash('sha256', $path.'|'.$h.'|'.$body);
        return 'payment:idem:api:' . $hash;
    }

    public function once(Request $req, callable $producer): array
    {
        $key = $this->keyFor($req);
        $cached = $this->store->get($key);
        if ($cached !== null) {
            /** @var array $arr */
            $arr = json_decode($cached, true) ?? [];
            return $arr;
        }
        /** @var array $result */
        $result = $producer();
        $this->store->put($key, json_encode($result), $this->ttlSec);
        return $result;
    }
}
