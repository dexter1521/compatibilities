<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\Api\V1\JwtService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = '/' . trim($request->getUri()->getPath(), '/');
        $public = ['/api/v1/auth/login', '/api/v1/auth/refresh'];
        if (in_array($path, $public, true)) {
            return null;
        }

        if (strpos($path, '/api/v1/') !== 0) {
            return null;
        }

        $header = $request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return service('response')->setStatusCode(401)->setJSON([
                'status' => 401,
                'success' => false,
                'data' => null,
                'message' => 'Token requerido.',
                'errors' => ['authorization' => ['bearer token requerido']],
            ]);
        }

        $jwt = new JwtService();
        $payload = $jwt->decode($m[1]);
        if ($payload === null) {
            return service('response')->setStatusCode(401)->setJSON([
                'status' => 401,
                'success' => false,
                'data' => null,
                'message' => 'Token invalido o expirado.',
                'errors' => ['authorization' => ['token invalido']],
            ]);
        }

        $request->user = $payload;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
