<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\Api\V1\SearchApiService;
use CodeIgniter\HTTP\ResponseInterface;

class SearchController extends BaseApiController
{
    private SearchApiService $service;

    public function __construct()
    {
        $this->service = new SearchApiService();
    }

    public function index(): ResponseInterface
    {
        $term = trim((string) ($this->request->getGet('q') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);
        $legacyPerPage = $this->request->getGet('per_page');
        $limit = (int) ($this->request->getGet('limit') ?? 50);

        if ($legacyPerPage !== null) {
            $limit = (int) $legacyPerPage;
        }

        if ($term === '' || mb_strlen($term) < 2) {
            return $this->respondValidationErrors([
                'q' => ['El tÃ©rmino debe contener al menos 2 caracteres.'],
            ]);
        }

        if ($limit < 1 || $limit > 50) {
            return $this->respondValidationErrors([
                'limit' => ['Debe estar entre 1 y 50.'],
            ]);
        }
        if ($page < 1) {
            return $this->respondValidationErrors([
                'page' => ['Debe ser mayor o igual a 1.'],
            ]);
        }

        $results = $this->service->search($term, $limit, $page);

        return $this->respondSuccess($results, 'BÃºsqueda completada.');
    }

    public function missed(): ResponseInterface
    {
        $query = [
            'page' => (int) ($this->request->getGet('page') ?? 1),
            'per_page' => (int) ($this->request->getGet('per_page') ?? 50),
            'sort_by' => (string) ($this->request->getGet('sort_by') ?? 'contador'),
            'sort_dir' => strtolower((string) ($this->request->getGet('sort_dir') ?? 'desc')),
            'q' => (string) ($this->request->getGet('q') ?? ''),
        ];

        $allowedSortBy = ['contador', 'ultima_busqueda_at', 'created_at'];
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

        return $this->respondSuccess($this->service->listSearchMissed($query), 'BÃºsquedas no encontradas obtenidas.');
    }

    public function confirmarCompatibilidad(int $id): ResponseInterface
    {
        $updated = $this->service->confirmCompatibilidad($id);

        if (!$updated) {
            return $this->respondError('Compatibilidad no encontrada.', null, ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->respondSuccess($updated, 'Compatibilidad confirmada correctamente.');
    }
}
