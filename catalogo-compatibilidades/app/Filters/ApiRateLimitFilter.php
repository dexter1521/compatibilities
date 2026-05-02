<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\Api\V1\RateLimitService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiRateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = '/' . trim($request->getUri()->getPath(), '/');
        if (strpos($path, '/api/v1/') !== 0) {
            return null;
        }

        $ip = $request->getIPAddress() ?: 'unknown';
        $method = strtoupper($request->getMethod());
        $limit = $this->resolveLimit($path, $method);
        $key = implode('|', [$ip, $method, $path]);

        $allowed = (new RateLimitService())->allow($key, $limit['maxAttempts'], $limit['windowSeconds']);
        if (!$allowed) {
            return service('response')->setStatusCode(429)->setJSON([
                'status' => 429,
                'success' => false,
                'data' => null,
                'message' => 'Demasiadas solicitudes. Intenta en un minuto.',
                'errors' => ['rate_limit' => ['excedido']],
            ]);
        }

        return null;
    }

    /**
     * @return array{maxAttempts:int,windowSeconds:int}
     */
    private function resolveLimit(string $path, string $method): array
    {
        if (strpos($path, '/api/v1/auth/') === 0) {
            return ['maxAttempts' => 12, 'windowSeconds' => 60];
        }

        if ($method === 'GET') {
            return ['maxAttempts' => 240, 'windowSeconds' => 60];
        }

        return ['maxAttempts' => 90, 'windowSeconds' => 60];
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
