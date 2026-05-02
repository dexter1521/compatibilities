<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\AuthService;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseApiController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function login(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $result = $this->service->login((string) $payload['email'], (string) $payload['password']);
        if ($result === null) {
            return $this->respondError('Credenciales inválidas.', null, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        return $this->respondSuccess($result, 'Login exitoso.');
    }

    public function refresh(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        if (!$this->validateData($payload, ['refresh_token' => 'required'])) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $result = $this->service->refresh((string) $payload['refresh_token']);
        if ($result === null) {
            return $this->respondError('Refresh token inválido.', null, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        return $this->respondSuccess($result, 'Token refrescado.');
    }

    public function me(): ResponseInterface
    {
        $user = $this->request->user ?? null;
        if (!$user) {
            return $this->respondError('No autenticado.', null, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        return $this->respondSuccess($user, 'Usuario autenticado.');
    }

    public function logout(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        if (!empty($payload['refresh_token'])) {
            $this->service->revokeRefreshToken((string) $payload['refresh_token']);
        }

        return $this->respondSuccess(null, 'Logout exitoso.');
    }
}
