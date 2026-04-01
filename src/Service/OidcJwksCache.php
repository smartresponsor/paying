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

    /** @return array{keys?: list<array<string, mixed>>} */
    public function get(): array
    {
        $url = (string) ($_ENV['OIDC_JWKS_URL'] ?? '');
        if ('' === $url) {
            return [];
        }

        if (is_file($this->cacheFile) && (time() - filemtime($this->cacheFile) < $this->ttl)) {
            return json_decode((string) file_get_contents($this->cacheFile), true) ?? [];
        }
        $context = stream_context_create(['http' => ['timeout' => 3]]);
        $json = @file_get_contents($url, false, $context);
        if (false !== $json) {
            @file_put_contents($this->cacheFile, $json);

            return json_decode($json, true) ?? [];
        }

        return [];
    }
}
