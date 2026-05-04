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
        $query = [
            'page' => (int) ($this->request->getGet('page') ?? 1),
            'per_page' => (int) ($this->request->getGet('per_page') ?? 20),
            'sort_by' => (string) ($this->request->getGet('sort_by') ?? 'alias'),
            'sort_dir' => strtolower((string) ($this->request->getGet('sort_dir') ?? 'asc')),
            'q' => (string) ($this->request->getGet('q') ?? ''),
            'motocicleta_id' => $this->request->getGet('motocicleta_id'),
            'marca_id' => $this->request->getGet('marca_id'),
        ];

        $allowedSortBy = ['id', 'alias', 'motocicleta_id', 'moto_modelo', 'marca_nombre', 'created_at', 'updated_at'];
        if (!in_array($query['sort_by'], $allowedSortBy, true)) {
            return $this->respondValidationErrors([
                'sort_by' => ['Valor no permitido.'],
            ]);
        }
        if (!in_array($query['sort_dir'], ['asc', 'desc'], true)) {
            return $this->respondValidationErrors([
                'sort_dir' => ['Valor no permitido.'],
            ]);
        }
        if ($query['motocicleta_id'] !== null && !is_numeric((string) $query['motocicleta_id'])) {
            return $this->respondValidationErrors([
                'motocicleta_id' => ['Valor no permitido.'],
            ]);
        }
        if ($query['marca_id'] !== null && !is_numeric((string) $query['marca_id'])) {
            return $this->respondValidationErrors([
                'marca_id' => ['Valor no permitido.'],
            ]);
        }

        return $this->respondSuccess($this->service->list($query), 'Listado de aliases obtenido.');
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

        $newId = $this->service->create([
            'motocicleta_id' => (int) $payload['motocicleta_id'],
            'alias' => $alias,
            'slug' => $this->service->makeSlug($alias),
        ]);

        return $this->respondSuccess($this->service->detailById($newId), 'Alias creado correctamente.', ResponseInterface::HTTP_CREATED);
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
        if (!$this->service->find($id)) {
            return $this->respondError('Alias no encontrado.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return $this->respondSuccess(null, 'Alias eliminado correctamente.');
    }
}
