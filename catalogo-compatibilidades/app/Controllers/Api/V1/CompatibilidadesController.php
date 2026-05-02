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
        return $this->respondSuccess(['items' => $this->service->list()], 'Listado de compatibilidades obtenido.');
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
