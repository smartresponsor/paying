<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\OidcJwksCacheInterface;

class OidcJwksCache implements OidcJwksCacheInterface
{
    private string $cacheFile;
    private int $ttl;

    public function __construct(string $cacheDir = __DIR__.'/../../../var/cache', int $ttl = 3600)
    {
        $this->cacheFile = rtrim($cacheDir, '/').'/jwks.json';
        $this->ttl = (int) ($_ENV['OIDC_JWKS_TTL'] ?? $ttl);
    }

    /** @return array{keys: list<array{n: string, e: string, kty?: string, kid?: string}>} */
    public function get(): array
    {
        $url = (string) ($_ENV['OIDC_JWKS_URL'] ?? '');
        if ('' === $url) {
            return ['keys' => []];
        }

        if (is_file($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->ttl)) {
            return $this->decode((string) file_get_contents($this->cacheFile));
        }

        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($url, false, $context);
        if (false !== $json) {
            @file_put_contents($this->cacheFile, $json);

            return $this->decode($json);
        }

        return ['keys' => []];
    }

    /** @return array{keys: list<array{n: string, e: string, kty?: string, kid?: string}>} */
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

            $normalized[] = $row;
        }

        return ['keys' => $normalized];
    }
}
