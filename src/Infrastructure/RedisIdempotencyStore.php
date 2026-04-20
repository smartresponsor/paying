<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure;

use App\Paying\ServiceInterface\IdempotencyStoreInterface;
use Redis;

/**
 * Stores payment idempotency keys in Redis-backed operational state.
 */
class RedisIdempotencyStore implements IdempotencyStoreInterface
{
    private \Redis $redis;

    /**
     * Boots the Redis-backed idempotency store from the supplied DSN.
     *
     * @throws \RedisException
     */
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
        if (!$this->redis->connect($host, $port, 1.5)) {
            throw new \RuntimeException('Redis connect failed');
        }
        if ($pass) {
            if (true !== $this->redis->auth($pass)) {
                throw new \RuntimeException('Redis auth failed');
            }
        }
        if ($db) {
            if (true !== $this->redis->select($db)) {
                throw new \RuntimeException('Redis database select failed');
            }
        }
    }

    /**
     * Loads a stored idempotency value when the key is present and not expired.
     *
     * @throws \RedisException
     */
    public function get(string $key): ?string
    {
        $val = $this->redis->get($key);

        return false === $val ? null : (string) $val;
    }

    /**
     * Stores or refreshes an idempotency value with a new expiration window.
     *
     * @throws \RedisException
     */
    public function put(string $key, string $value, int $ttlSec): void
    {
        $this->redis->set($key, $value, $ttlSec);
    }

    /**
     * Returns zero because Redis expiration is enforced natively.
     */
    public function purgeExpired(): int
    {
        // Redis handles expiration automatically
        return 0;
    }
}
