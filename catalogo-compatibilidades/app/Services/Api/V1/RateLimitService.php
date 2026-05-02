<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

class RateLimitService
{
    public function allow(string $key, int $maxAttempts = 120, int $windowSeconds = 60): bool
    {
        $cache = cache();
        $bucketKey = 'rl_' . hash('sha256', $key);
        $current = (int) ($cache->get($bucketKey) ?? 0);

        if ($current >= $maxAttempts) {
            return false;
        }

        $cache->save($bucketKey, $current + 1, $windowSeconds);

        return true;
    }
}
