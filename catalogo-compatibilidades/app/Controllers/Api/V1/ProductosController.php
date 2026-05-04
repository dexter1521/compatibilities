<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\ProductoService;
use CodeIgniter\HTTP\ResponseInterface;

class ProductosController extends BaseApiController
{
    private ProductoService $service;

    public function __construct()
    {
        $this->service = new ProductoService();
    }

    public function index(): ResponseInterface
    {
        $query = [
            'page' => (int) ($this->request->getGet('page') ?? 1),
            'per_page' => (int) ($this->request->getGet('per_page') ?? 20),
            'sort_by' => (string) ($this->request->getGet('sort_by') ?? 'id'),
            'sort_dir' => strtolower((string) ($this->request->getGet('sort_dir') ?? 'desc')),
            'q' => (string) ($this->request->getGet('q') ?? ''),
            'proveedor_id' => $this->request->getGet('proveedor_id'),
            'pieza_maestra_id' => $this->request->getGet('pieza_maestra_id'),
            'activo' => $this->request->getGet('activo'),
            'enrich_estado' => $this->request->getGet('enrich_estado'),
        ];

        $allowedSortBy = ['id', 'nombre', 'clave_proveedor', 'created_at', 'updated_at', 'proveedor_nombre'];
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
        if ($query['proveedor_id'] !== null && !is_numeric((string) $query['proveedor_id'])) {
            return $this->respondValidationErrors([
                'proveedor_id' => ['Valor no permitido.'],
            ]);
        }
        if ($query['pieza_maestra_id'] !== null && !is_numeric((string) $query['pieza_maestra_id'])) {
            return $this->respondValidationErrors([
                'pieza_maestra_id' => ['Valor no permitido.'],
            ]);
        }
        if ($query['activo'] !== null && !in_array((string) $query['activo'], ['0', '1'], true)) {
            return $this->respondValidationErrors([
                'activo' => ['Valor no permitido.'],
            ]);
        }

        $allowedEnrich = [null, '', 'ok', 'sin_tipo', 'sin_moto', 'sin_ambos'];
        if (!in_array($query['enrich_estado'], $allowedEnrich, true)) {
            return $this->respondValidationErrors([
                'enrich_estado' => ['Valor no permitido.'],
            ]);
        }

        return $this->respondSuccess($this->service->list($query), 'Listado de productos obtenido.');
    }

    public function show(int $id): ResponseInterface
    {
        $row = $this->service->find($id);

        if (!$row) {
            return $this->respondError('Producto no encontrado.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->respondSuccess($row, 'Producto obtenido.');
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($payload)) {
            $payload = $this->request->getRawInput();
        }

        $rules = [
            'proveedor_id'    => 'required|is_natural_no_zero',
            'clave_proveedor' => 'required|max_length[100]',
            'nombre'          => 'required|max_length[500]',
            'pieza_maestra_id'=> 'permit_empty|is_natural_no_zero',
            'activo'          => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $proveedorId = (int) ($payload['proveedor_id'] ?? 0);
        $clave = trim((string) ($payload['clave_proveedor'] ?? ''));

        if ($this->service->existsByProveedorClave($proveedorId, $clave)) {
            return $this->respondError('Ya existe un producto con esa clave para el proveedor.', [
                'clave_proveedor' => ['duplicada para proveedor'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $nombre = trim((string) ($payload['nombre'] ?? ''));

        $insertData = [
            'proveedor_id'     => $proveedorId,
            'clave_proveedor'  => $clave,
            'nombre'           => $nombre,
            'slug'             => $this->service->makeUniqueSlug($clave . '-' . $nombre),
            'pieza_maestra_id' => ($payload['pieza_maestra_id'] ?? '') !== '' ? (int) $payload['pieza_maestra_id'] : null,
            'activo'           => (int) ($payload['activo'] ?? 1),
            'enrich_estado'    => $payload['enrich_estado'] ?? null,
        ];

        $newId = $this->service->create($insertData);

        return $this->respondSuccess($this->service->find($newId), 'Producto creado correctamente.', ResponseInterface::HTTP_CREATED);
    }

    public function update(int $id): ResponseInterface
    {
        if (!$this->service->find($id)) {
            return $this->respondError('Producto no encontrado.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $payload = $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = $this->request->getRawInput();
        }

        $rules = [
            'proveedor_id'    => 'permit_empty|is_natural_no_zero',
            'clave_proveedor' => 'permit_empty|max_length[100]',
            'nombre'          => 'permit_empty|max_length[500]',
            'pieza_maestra_id'=> 'permit_empty|is_natural_no_zero',
            'activo'          => 'permit_empty|in_list[0,1]',
            'enrich_estado'   => 'permit_empty|in_list[ok,sin_tipo,sin_moto,sin_ambos]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $actual = $this->service->find($id);

        $proveedorId = isset($payload['proveedor_id']) ? (int) $payload['proveedor_id'] : (int) $actual['proveedor_id'];
        $clave = isset($payload['clave_proveedor']) ? trim((string) $payload['clave_proveedor']) : (string) $actual['clave_proveedor'];

        if ($this->service->existsByProveedorClave($proveedorId, $clave, $id)) {
            return $this->respondError('Ya existe un producto con esa clave para el proveedor.', [
                'clave_proveedor' => ['duplicada para proveedor'],
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $nombre = isset($payload['nombre']) ? trim((string) $payload['nombre']) : (string) $actual['nombre'];

        $updateData = [
            'proveedor_id'     => $proveedorId,
            'clave_proveedor'  => $clave,
            'nombre'           => $nombre,
            'slug'             => $this->service->makeUniqueSlug($clave . '-' . $nombre, $id),
            'pieza_maestra_id' => array_key_exists('pieza_maestra_id', $payload) && $payload['pieza_maestra_id'] !== ''
                ? (int) $payload['pieza_maestra_id']
                : null,
            'activo'           => array_key_exists('activo', $payload) ? (int) $payload['activo'] : (int) $actual['activo'],
            'enrich_estado'    => $payload['enrich_estado'] ?? $actual['enrich_estado'],
        ];

        $this->service->update($id, $updateData);

        return $this->respondSuccess($this->service->find($id), 'Producto actualizado correctamente.');
    }

    public function delete(int $id): ResponseInterface
    {
        if (!$this->service->find($id)) {
            return $this->respondError('Producto no encontrado.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return $this->respondSuccess(null, 'Producto eliminado correctamente.');
    }
}
