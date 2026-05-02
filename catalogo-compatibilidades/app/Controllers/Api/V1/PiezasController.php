<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\PiezaApiService;
use CodeIgniter\HTTP\ResponseInterface;

class PiezasController extends BaseApiController
{
    private PiezaApiService $service;

    public function __construct()
    {
        $this->service = new PiezaApiService();
    }

    public function index(): ResponseInterface
    {
        return $this->respondSuccess(['items' => $this->service->list()], 'Listado de piezas obtenido.');
    }

    public function show(int $id): ResponseInterface
    {
        $row = $this->service->find($id);
        if (!$row) {
            return $this->respondError('Pieza no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->respondSuccess($row, 'Pieza obtenida.');
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        if (!$this->validateData($payload, ['nombre' => 'required|max_length[180]'])) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $nombre = trim((string) $payload['nombre']);

        if ($this->service->existsByNombre($nombre)) {
            return $this->respondError('Ya existe una pieza con ese nombre.', [
                'nombre' => ['duplicado'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $id = $this->service->create([
            'nombre' => $nombre,
            'slug' => $this->service->makeSlug($nombre),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Pieza creada correctamente.', ResponseInterface::HTTP_CREATED);
    }

    public function update(int $id): ResponseInterface
    {
        $actual = $this->service->find($id);
        if (!$actual) {
            return $this->respondError('Pieza no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        if (!$this->validateData($payload, ['nombre' => 'required|max_length[180]'])) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $nombre = trim((string) $payload['nombre']);

        if ($this->service->existsByNombre($nombre, $id)) {
            return $this->respondError('Ya existe una pieza con ese nombre.', [
                'nombre' => ['duplicado'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $this->service->update($id, [
            'nombre' => $nombre,
            'slug' => $this->service->makeSlug($nombre, $id),
        ]);

        return $this->respondSuccess($this->service->find($id), 'Pieza actualizada correctamente.');
    }

    public function delete(int $id): ResponseInterface
    {
        if (!$this->service->find($id)) {
            return $this->respondError('Pieza no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return $this->respondSuccess(null, 'Pieza eliminada correctamente.');
    }
}
