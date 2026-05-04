<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\CompatibilidadApiService;
use CodeIgniter\HTTP\ResponseInterface;

class CompatibilidadesController extends BaseApiController
{
    private CompatibilidadApiService $service;

    public function __construct()
    {
        $this->service = new CompatibilidadApiService();
    }

    public function index(): ResponseInterface
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);

        $query = [
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => (string) ($this->request->getGet('sort_by') ?? 'id'),
            'sort_dir' => strtolower((string) ($this->request->getGet('sort_dir') ?? 'desc')),
            'q' => (string) ($this->request->getGet('q') ?? ''),
            'pieza_maestra_id' => $this->request->getGet('pieza_maestra_id'),
            'motocicleta_id' => $this->request->getGet('motocicleta_id'),
            'marca_id' => $this->request->getGet('marca_id'),
            'confirmada' => $this->request->getGet('confirmada'),
        ];

        $allowedSortBy = ['id', 'pieza_nombre', 'moto_modelo', 'marca_nombre', 'confirmada', 'contador_confirmaciones', 'created_at', 'updated_at'];
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
        if ($page < 1) {
            return $this->respondValidationErrors([
                'page' => ['Debe ser mayor o igual a 1.'],
            ]);
        }
        if ($perPage < 1 || $perPage > 100) {
            return $this->respondValidationErrors([
                'per_page' => ['Debe estar entre 1 y 100.'],
            ]);
        }
        if ($query['pieza_maestra_id'] !== null && !is_numeric((string) $query['pieza_maestra_id'])) {
            return $this->respondValidationErrors([
                'pieza_maestra_id' => ['Valor no permitido.'],
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
        if ($query['confirmada'] !== null && !in_array((string) $query['confirmada'], ['0', '1'], true)) {
            return $this->respondValidationErrors([
                'confirmada' => ['Valor no permitido.'],
            ]);
        }

        return $this->respondSuccess($this->service->list($query), 'Listado de compatibilidades obtenido.');
    }

    public function show(int $id): ResponseInterface
    {
        $row = $this->service->find($id);
        if (!$row) {
            return $this->respondError('Compatibilidad no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->respondSuccess($row, 'Compatibilidad obtenida.');
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'pieza_maestra_id' => 'required|is_natural_no_zero',
            'motocicleta_id' => 'required|is_natural_no_zero',
            'confirmada' => 'permit_empty|in_list[0,1]',
            'contador_confirmaciones' => 'permit_empty|is_natural',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $piezaId = (int) $payload['pieza_maestra_id'];
        $motoId = (int) $payload['motocicleta_id'];

        if ($this->service->existsPar($piezaId, $motoId)) {
            return $this->respondError('Esta combinación pieza-moto ya existe.', [
                'pieza_maestra_id' => ['duplicado con motocicleta_id'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $id = $this->service->create([
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => (int) ($payload['confirmada'] ?? 0),
            'contador_confirmaciones' => (int) ($payload['contador_confirmaciones'] ?? 0),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Compatibilidad creada correctamente.', ResponseInterface::HTTP_CREATED);
    }

    public function update(int $id): ResponseInterface
    {
        $actual = $this->service->find($id);
        if (!$actual) {
            return $this->respondError('Compatibilidad no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $rules = [
            'pieza_maestra_id' => 'permit_empty|is_natural_no_zero',
            'motocicleta_id' => 'permit_empty|is_natural_no_zero',
            'confirmada' => 'permit_empty|in_list[0,1]',
            'contador_confirmaciones' => 'permit_empty|is_natural',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $piezaId = isset($payload['pieza_maestra_id']) ? (int) $payload['pieza_maestra_id'] : (int) $actual['pieza_maestra_id'];
        $motoId = isset($payload['motocicleta_id']) ? (int) $payload['motocicleta_id'] : (int) $actual['motocicleta_id'];

        if ($this->service->existsPar($piezaId, $motoId, $id)) {
            return $this->respondError('Esta combinación pieza-moto ya existe.', [
                'pieza_maestra_id' => ['duplicado con motocicleta_id'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $this->service->update($id, [
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => array_key_exists('confirmada', $payload) ? (int) $payload['confirmada'] : (int) $actual['confirmada'],
            'contador_confirmaciones' => array_key_exists('contador_confirmaciones', $payload) ? (int) $payload['contador_confirmaciones'] : (int) $actual['contador_confirmaciones'],
        ]);

        return $this->respondSuccess($this->service->find($id), 'Compatibilidad actualizada correctamente.');
    }

    public function delete(int $id): ResponseInterface
    {
        if (!$this->service->find($id)) {
            return $this->respondError('Compatibilidad no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return $this->respondSuccess(null, 'Compatibilidad eliminada correctamente.');
    }
}
