<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\MotoApiService;
use CodeIgniter\HTTP\ResponseInterface;

class MotocicletasController extends BaseApiController
{
    private MotoApiService $service;

    public function __construct()
    {
        $this->service = new MotoApiService();
    }

    public function index(): ResponseInterface
    {
        $query = [
            'page' => (int) ($this->request->getGet('page') ?? 1),
            'per_page' => (int) ($this->request->getGet('per_page') ?? 20),
            'sort_by' => (string) ($this->request->getGet('sort_by') ?? 'id'),
            'sort_dir' => strtolower((string) ($this->request->getGet('sort_dir') ?? 'desc')),
            'q' => (string) ($this->request->getGet('q') ?? ''),
            'marca_id' => $this->request->getGet('marca_id'),
        ];

        $allowedSortBy = ['id', 'modelo', 'marca_id', 'marca_nombre', 'anio_desde', 'anio_hasta', 'cilindrada', 'created_at', 'updated_at'];
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
        if ($query['marca_id'] !== null && !is_numeric((string) $query['marca_id'])) {
            return $this->respondValidationErrors([
                'marca_id' => ['Valor no permitido.'],
            ]);
        }

        return $this->respondSuccess($this->service->list($query), 'Listado de motocicletas obtenido.');
    }

    public function show(int $id): ResponseInterface
    {
        $row = $this->service->find($id);
        if (!$row) {
            return $this->respondError('Motocicleta no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->respondSuccess($row, 'Motocicleta obtenida.');
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'marca_id' => 'required|is_natural_no_zero',
            'modelo' => 'required|max_length[150]',
            'anio_desde' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'anio_hasta' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'cilindrada' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $marcaId = (int) $payload['marca_id'];
        $modelo = trim((string) $payload['modelo']);

        if ($this->service->existsByMarcaModelo($marcaId, $modelo)) {
            return $this->respondError('Ya existe esa moto con esa marca.', [
                'modelo' => ['duplicado por marca'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $id = $this->service->create([
            'marca_id' => $marcaId,
            'modelo' => $modelo,
            'anio_desde' => ($payload['anio_desde'] ?? '') !== '' ? (int) $payload['anio_desde'] : null,
            'anio_hasta' => ($payload['anio_hasta'] ?? '') !== '' ? (int) $payload['anio_hasta'] : null,
            'cilindrada' => ($payload['cilindrada'] ?? '') !== '' ? trim((string) $payload['cilindrada']) : null,
            'slug' => $this->service->makeSlug($modelo, $marcaId),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Motocicleta creada correctamente.', ResponseInterface::HTTP_CREATED);
    }

    public function update(int $id): ResponseInterface
    {
        $actual = $this->service->find($id);
        if (!$actual) {
            return $this->respondError('Motocicleta no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'marca_id' => 'permit_empty|is_natural_no_zero',
            'modelo' => 'permit_empty|max_length[150]',
            'anio_desde' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'anio_hasta' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'cilindrada' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $marcaId = isset($payload['marca_id']) ? (int) $payload['marca_id'] : (int) $actual['marca_id'];
        $modelo = isset($payload['modelo']) ? trim((string) $payload['modelo']) : (string) $actual['modelo'];

        if ($this->service->existsByMarcaModelo($marcaId, $modelo, $id)) {
            return $this->respondError('Ya existe esa moto con esa marca.', [
                'modelo' => ['duplicado por marca'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $this->service->update($id, [
            'marca_id' => $marcaId,
            'modelo' => $modelo,
            'anio_desde' => array_key_exists('anio_desde', $payload) ? (($payload['anio_desde'] ?? '') !== '' ? (int) $payload['anio_desde'] : null) : $actual['anio_desde'],
            'anio_hasta' => array_key_exists('anio_hasta', $payload) ? (($payload['anio_hasta'] ?? '') !== '' ? (int) $payload['anio_hasta'] : null) : $actual['anio_hasta'],
            'cilindrada' => array_key_exists('cilindrada', $payload) ? (($payload['cilindrada'] ?? '') !== '' ? trim((string) $payload['cilindrada']) : null) : $actual['cilindrada'],
            'slug' => $this->service->makeSlug($modelo, $marcaId, $id),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Motocicleta actualizada correctamente.');
    }

    public function delete(int $id): ResponseInterface
    {
        if (!$this->service->find($id)) {
            return $this->respondError('Motocicleta no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return $this->respondSuccess(null, 'Motocicleta eliminada correctamente.');
    }
}
