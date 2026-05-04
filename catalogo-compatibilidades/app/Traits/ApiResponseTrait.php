<?php

declare(strict_types=1);

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

trait ApiResponseTrait
{
    protected function respondSuccess(mixed $data, string $message = 'Consulta exitosa', int $status = ResponseInterface::HTTP_OK): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status'  => $status,
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
        ]);
    }

    protected function respondError(string $message, ?array $errors = null, int $status = ResponseInterface::HTTP_BAD_REQUEST): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status'  => $status,
            'success' => false,
            'data'    => null,
            'message' => $message,
            'errors'  => $errors,
        ]);
    }

    protected function respondValidationErrors(array $errors, string $message = 'Validación fallida'): ResponseInterface
    {
        return $this->respondError($message, $errors, ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
    }
}

