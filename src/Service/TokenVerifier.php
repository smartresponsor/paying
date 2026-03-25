<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\TokenVerifierInterface;

class TokenVerifier implements TokenVerifierInterface
{
    public function __construct(private readonly OidcJwksCache $jwks)
    {
    }

    /** @return array<string, mixed> */
    public function verify(string $jwt): array
    {
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $this->split($jwt);
        $header = $this->json($this->b64($headerEncoded));
        $payload = $this->json($this->b64($payloadEncoded));
        $signature = $this->b64bin($signatureEncoded);

        $algorithm = (string) ($header['alg'] ?? '');
        if ('RS256' !== $algorithm) {
            throw new \RuntimeException('alg-not-supported');
        }

        $keyId = (string) ($header['kid'] ?? '');
        $jwk = $this->findKey($keyId);
        $pem = $this->jwkToPem($jwk);
        $verified = \openssl_verify($headerEncoded.'.'.$payloadEncoded, $signature, $pem, OPENSSL_ALGO_SHA256);
        if (1 !== $verified) {
            throw new \RuntimeException('jwt-signature-invalid');
        }

        $now = time();
        if (isset($payload['exp']) && (int) $payload['exp'] < $now) {
            throw new \RuntimeException('jwt-expired');
        }
        if (isset($payload['nbf']) && (int) $payload['nbf'] > $now) {
            throw new \RuntimeException('jwt-not-before');
        }

        $issuer = (string) ($_ENV['OIDC_ISS'] ?? '');
        if ('' !== $issuer && (string) ($payload['iss'] ?? '') !== $issuer) {
            throw new \RuntimeException('iss-mismatch');
        }

        $audience = (string) ($_ENV['OIDC_AUD'] ?? '');
        if ('' !== $audience) {
            $audClaim = $payload['aud'] ?? null;
            $audiences = is_array($audClaim) ? $audClaim : [$audClaim];
            if (!in_array($audience, $audiences, true)) {
                throw new \RuntimeException('aud-mismatch');
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $claims
     * @param list<string>         $required
     */
    public function hasScopes(array $claims, array $required, bool $any = false): bool
    {
        $scopes = [];
        if (isset($claims['scope']) && is_string($claims['scope'])) {
            $scopes = array_values(array_filter(explode(' ', $claims['scope']), static fn (string $scope): bool => '' !== $scope));
        } elseif (isset($claims['scp']) && is_array($claims['scp'])) {
            $scopes = array_values(array_map('strval', $claims['scp']));
        }
        if ([] === $required) {
            return true;
        }
        if ($any) {
            return count(array_intersect($required, $scopes)) > 0;
        }

        return 0 === count(array_diff($required, $scopes));
    }

    /** @return array{n: string, e: string, kty?: string, kid?: string} */
    private function findKey(string $kid): array
    {
        $jwks = $this->jwks->get();
        $keys = $jwks['keys'] ?? [];
        foreach ($keys as $key) {
            if (!is_array($key)) {
                continue;
            }

            $normalized = [
                'n' => (string) ($key['n'] ?? ''),
                'e' => (string) ($key['e'] ?? ''),
            ];

            $keyId = trim((string) ($key['kid'] ?? ''));
            if ('' !== $keyId) {
                $normalized['kid'] = $keyId;
            }

            $kty = trim((string) ($key['kty'] ?? ''));
            if ('' !== $kty) {
                $normalized['kty'] = $kty;
            }

            if ('' !== $normalized['n'] && '' !== $normalized['e'] && $keyId === $kid) {
                return $normalized;
            }
        }

        if ('' === $kid) {
            foreach ($keys as $key) {
                if (!is_array($key)) {
                    continue;
                }

                $normalized = [
                    'n' => (string) ($key['n'] ?? ''),
                    'e' => (string) ($key['e'] ?? ''),
                ];
                if ('' !== $normalized['n'] && '' !== $normalized['e']) {
                    $keyId = trim((string) ($key['kid'] ?? ''));
                    if ('' !== $keyId) {
                        $normalized['kid'] = $keyId;
                    }

                    $kty = trim((string) ($key['kty'] ?? ''));
                    if ('' !== $kty) {
                        $normalized['kty'] = $kty;
                    }

                    return $normalized;
                }
            }
        }

        throw new \RuntimeException('kid-not-found');
    }

    /** @param array{n: string, e: string, kty?: string, kid?: string} $jwk */
    private function jwkToPem(array $jwk): string
    {
        $n = $this->b64bin((string) $jwk['n']);
        $e = $this->b64bin((string) $jwk['e']);
        $bn = $this->derInt($n);
        $be = $this->derInt($e);
        $sequence = "\x30".$this->derLen(strlen($bn) + strlen($be)).$bn.$be;
        $bitString = "\x03".$this->derLen(strlen($sequence) + 1)."\x00".$sequence;
        $algorithmIdentifier = "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00";
        $subjectPublicKeyInfo = "\x30".$this->derLen(strlen($algorithmIdentifier) + strlen($bitString)).$algorithmIdentifier.$bitString;

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")."-----END PUBLIC KEY-----\n";
    }

    private function derInt(string $bytes): string
    {
        if ('' === $bytes || $bytes[0] >= "\x80") {
            $bytes = "\x00".$bytes;
        }

        return "\x02".$this->derLen(strlen($bytes)).$bytes;
    }

    private function derLen(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }
        $packed = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($packed)).$packed;
    }

    /** @return array{0: string, 1: string, 2: string} */
    private function split(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (3 !== count($parts)) {
            throw new \RuntimeException('jwt-format');
        }

        return [(string) $parts[0], (string) $parts[1], (string) $parts[2]];
    }

    /** @return array<string, mixed> */
    private function json(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('json-decode');
        }

        return $decoded;
    }

    private function b64(string $value): string
    {
        $value = strtr($value, '-_', '+/');

        return (string) base64_decode($value.'==', true);
    }

    private function b64bin(string $value): string
    {
        $value = strtr($value, '-_', '+/');

        return (string) base64_decode($value.'==', true);
    }
}
