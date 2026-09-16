<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\ServiceInterface\PaymentOidcJwksCacheInterface;

/**
 * Provides the oidc jwks cache service used by the payment lifecycle and operator-facing flows.
 */
class PaymentOidcJwksCache implements PaymentOidcJwksCacheInterface
{
    private string $cacheFile;
    private int $ttl;
    private string $jwksUrl;

    public function __construct(string $cacheDir = __DIR__.'/../../../var/cache', int $ttl = 3600, ?string $jwksUrl = null)
    {
        $this->cacheFile = rtrim($cacheDir, '/').'/jwks.json';
        $this->ttl = $ttl;
        $this->jwksUrl = trim($jwksUrl ?? '');
    }

    /**
     * Returns the value exposed by the get accessor.
     */
    public function get(): array
    {
        if ('' === $this->jwksUrl) {
            return ['keys' => []];
        }

        if (is_file($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->ttl)) {
            return $this->decode((string) file_get_contents($this->cacheFile));
        }

        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($this->jwksUrl, false, $context);
        if (false !== $json) {
            $cacheDir = dirname($this->cacheFile);
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }
            @file_put_contents($this->cacheFile, $json, LOCK_EX);

            return $this->decode($json);
        }

        return ['keys' => []];
    }

    /** @return array{keys: list<array{n: string, e: string, kty?: string, kid?: string, use?: string, alg?: string}>} */
    private function decode(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return ['keys' => []];
        }

        $keys = $decoded['keys'] ?? null;
        if (!is_array($keys)) {
            return ['keys' => []];
        }

        $normalized = [];

        foreach ($keys as $key) {
            if (!is_array($key)) {
                continue;
            }

            $n = trim((string) ($key['n'] ?? ''));
            $e = trim((string) ($key['e'] ?? ''));
            if ('' === $n || '' === $e) {
                continue;
            }

            $row = ['n' => $n, 'e' => $e];

            $kid = trim((string) ($key['kid'] ?? ''));
            if ('' !== $kid) {
                $row['kid'] = $kid;
            }

            $kty = trim((string) ($key['kty'] ?? ''));
            if ('' !== $kty) {
                $row['kty'] = $kty;
            }

            $use = trim((string) ($key['use'] ?? ''));
            if ('' !== $use) {
                $row['use'] = $use;
            }

            $alg = trim((string) ($key['alg'] ?? ''));
            if ('' !== $alg) {
                $row['alg'] = $alg;
            }

            $normalized[] = $row;
        }

        return ['keys' => $normalized];
    }
}
