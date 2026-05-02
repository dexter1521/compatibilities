<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

class JwtService
{
    private string $secret;
    private string $algo = 'sha256';
    private int $ttlAccess = 3600;

    public function __construct()
    {
        $this->secret = (string) (getenv('JWT_SECRET') ?: 'change-this-secret-in-env');

        $env = (string) (getenv('CI_ENVIRONMENT') ?: 'production');
        if ($env === 'production' && ($this->secret === '' || $this->secret === 'change-this-secret-in-env')) {
            throw new \RuntimeException('JWT_SECRET no configurado para entorno de producción.');
        }
    }

    public function issueAccessToken(array $claims): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttlAccess,
            'typ' => 'access',
        ]);

        return $this->encode($payload);
    }

    public function decode(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$h64, $p64, $s64] = $parts;
        $sig = $this->base64UrlDecode($s64);
        $data = $h64 . '.' . $p64;
        $expected = hash_hmac($this->algo, $data, $this->secret, true);

        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($p64), true);
        if (!is_array($payload)) {
            return null;
        }

        $now = time();
        if (isset($payload['nbf']) && $now < (int) $payload['nbf']) {
            return null;
        }
        if (isset($payload['exp']) && $now >= (int) $payload['exp']) {
            return null;
        }

        return $payload;
    }

    private function encode(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $h64 = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $p64 = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $sig = hash_hmac($this->algo, $h64 . '.' . $p64, $this->secret, true);
        $s64 = $this->base64UrlEncode($sig);

        return $h64 . '.' . $p64 . '.' . $s64;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
