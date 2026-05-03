<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\AliasApiService;
use CodeIgniter\HTTP\ResponseInterface;

class AliasesController extends BaseApiController
{
    private AliasApiService $service;

    public function __construct()
    {
        $this->service = new AliasApiService();
    }

    public function index(): ResponseInterface
    {
        return $this->respondSuccess(['items' => $this->service->list()], 'Listado de aliases obtenido.');
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'motocicleta_id' => 'required|is_natural_no_zero',
            'alias' => 'required|max_length[180]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $alias = trim((string) $payload['alias']);
        if ($this->service->existsByAlias($alias)) {
            return $this->respondError('Alias ya registrado.', ['alias' => ['duplicado']], ResponseInterface::HTTP_CONFLICT);
        }

        $this->service->create([
            'motocicleta_id' => (int) $payload['motocicleta_id'],
            'alias' => $alias,
            'slug' => $this->service->makeSlug($alias),
        ]);

        return $this->respondSuccess(['items' => $this->service->list()], 'Alias creado correctamente.', ResponseInterface::HTTP_CREATED);
    }

    public function update(int $id): ResponseInterface
    {
        $actual = $this->service->find($id);
        if (!$actual) {
            return $this->respondError('Alias no encontrado.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        if (!$this->validateData($payload, [
            'motocicleta_id' => 'required|is_natural_no_zero',
            'alias' => 'required|max_length[180]',
        ])) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $alias = trim((string) $payload['alias']);
        if ($this->service->existsByAlias($alias, $id)) {
            return $this->respondError('Alias ya registrado.', [
                'alias' => ['duplicado'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $this->service->update($id, [
            'motocicleta_id' => (int) $payload['motocicleta_id'],
            'alias' => $alias,
            'slug' => $this->service->makeSlug($alias, $id),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Alias actualizado correctamente.');
    }

    public function delete(int $id): ResponseInterface
    {
        $this->service->delete($id);

        return $this->respondSuccess(null, 'Alias eliminado correctamente.');
    }
}
