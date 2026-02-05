<?php
declare(strict_types=1);

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 */

namespace App\Service\Payment;

use App\ServiceInterface\Payment\TokenVerifierInterface;

class TokenVerifier implements TokenVerifierInterface
{
    public function __construct(private readonly OidcJwksCache $jwks) {}

    public function verify(string $jwt): array
    {
        [$h, $p, $s] = $this->split($jwt);
        $header = $this->json($this->b64($h));
        $payload = $this->json($this->b64($p));
        $sig = $this->b64bin($s);

        $alg = (string)($header['alg'] ?? '');
        if ($alg !== 'RS256') { throw new \RuntimeException('alg-not-supported'); }

        $kid = (string)($header['kid'] ?? '');
        $jwk = $this->findKey($kid);
        $pem = $this->jwkToPem($jwk);
        $ok = \openssl_verify($h.'.'.$p, $sig, $pem, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) { throw new \RuntimeException('jwt-signature-invalid'); }

        $now = time();
        if (isset($payload['exp']) && (int)$payload['exp'] < $now) { throw new \RuntimeException('jwt-expired'); }
        if (isset($payload['nbf']) && (int)$payload['nbf'] > $now) { throw new \RuntimeException('jwt-not-before'); }

        $iss = (string)($_ENV['OIDC_ISS'] ?? '');
        if ($iss !== '' && (string)($payload['iss'] ?? '') !== $iss) { throw new \RuntimeException('iss-mismatch'); }

        $aud = (string)($_ENV['OIDC_AUD'] ?? '');
        if ($aud !== '') {
            $audClaim = $payload['aud'] ?? null;
            $audList = is_array($audClaim) ? $audClaim : [$audClaim];
            if (!in_array($aud, $audList, true)) { throw new \RuntimeException('aud-mismatch'); }
        }

        return $payload;
    }

    public function hasScopes(array $claims, array $required, bool $any = false): bool
    {
        $sc = [];
        if (isset($claims['scope']) && is_string($claims['scope'])) {
            $sc = array_filter(explode(' ', $claims['scope']), 'strlen');
        } elseif (isset($claims['scp']) && is_array($claims['scp'])) {
            $sc = array_values(array_map('strval', $claims['scp']));
        }
        if (!$required) return true;
        if ($any) { return count(array_intersect($required, $sc)) > 0; }
        return count(array_diff($required, $sc)) === 0;
    }

    /** @return array{n:string,e:string,kty?:string} */
    private function findKey(string $kid): array
    {
        $jwks = $this->jwks->get();
        $keys = $jwks['keys'] ?? [];
        foreach ($keys as $k) {
            if ((string)($k['kid'] ?? '') === $kid) return $k;
        }
        if ($kid === '' && isset($keys[0])) return $keys[0];
        throw new \RuntimeException('kid-not-found');
    }

    private function jwkToPem(array $jwk): string
    {
        $n = $this->b64bin((string)$jwk['n']);
        $e = $this->b64bin((string)$jwk['e']);
        // DER sequence: 0x30 len { 0x02 len n | 0x02 len e }
        $bn = $this->derInt($n);
        $be = $this->derInt($e);
        $seq = "\x30". $this->derLen(strlen($bn)+strlen($be)) . $bn . $be;
        $bit = "\x03". $this->derLen(strlen($seq)+1) . "\x00" . $seq;
        $alg = "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00"; // rsaEncryption OID
        $spki = "\x30". $this->derLen(strlen($alg)+strlen($bit)) . $alg . $bit;
        $pem = "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($spki),64,"\n")."-----END PUBLIC KEY-----\n";
        return $pem;
    }

    private function derInt(string $x): string
    {
        if ($x === '' || $x[0] >= "\x80") { $x = "\x00".$x; }
        return "\x02". $this->derLen(len($x := $x)) . $x;
    }

    private function derLen(int $l): string
    {
        if ($l < 128) return chr($l);
        $s = ltrim(pack('N', $l), "\x00");
        return chr(0x80 | strlen($s)) . $s;
    }

    private function split(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) throw new \RuntimeException('jwt-format');
        return $parts;
    }

    /** @return array<string,mixed> */
    private function json(string $s): array
    {
        $a = json_decode($s, true);
        if (!is_array($a)) throw new \RuntimeException('json-decode');
        return $a;
    }

    private function b64(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        return (string)base64_decode($s.'==', true);
    }

    private function b64bin(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        return (string)base64_decode($s.'==', true);
    }
}
