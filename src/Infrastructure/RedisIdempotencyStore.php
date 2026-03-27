<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure;

use App\ServiceInterface\IdempotencyStoreInterface;

class RedisIdempotencyStore implements IdempotencyStoreInterface
{
    private \Redis $redis;

    public function __construct(string $url)
    {
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'redis') {
            throw new \InvalidArgumentException('Bad REDIS_URL');
        }
        $host = $parts['host'] ?? '127.0.0.1';
        $port = (int) ($parts['port'] ?? 6379);
        $pass = $parts['pass'] ?? null;
        $db = isset($parts['path']) ? (int) trim($parts['path'], '/') : 0;

        $this->redis = new \Redis();
        if (!@$this->redis->connect($host, $port, 1.5)) {
            throw new \RuntimeException('Redis connect failed');
        }
        if ($pass) {
            @$this->redis->auth($pass);
        }
        if ($db) {
            @$this->redis->select($db);
        }
    }

    public function get(string $key): ?string
    {
        $val = $this->redis->get($key);

        return false === $val ? null : (string) $val;
    }

    public function put(string $key, string $value, int $ttlSec): void
    {
        $this->redis->set($key, $value, $ttlSec);
    }

    public function purgeExpired(): int
    {
        // Redis handles expiration automatically
        return 0;
    }
}
