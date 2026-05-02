<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedRoles = is_array($arguments) ? $arguments : [];
        if ($allowedRoles === []) {
            return null;
        }

        $user = $request->user ?? null;
        $role = is_array($user) ? (string) ($user['role'] ?? '') : '';

        if ($role === '' || !in_array($role, $allowedRoles, true)) {
            return service('response')->setStatusCode(403)->setJSON([
                'status' => 403,
                'success' => false,
                'data' => null,
                'message' => 'No tienes permisos para realizar esta acción.',
                'errors' => ['authorization' => ['rol insuficiente']],
            ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
