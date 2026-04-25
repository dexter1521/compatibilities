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

        if ($term === '' || mb_strlen($term) < 2) {
            return $this->respondValidationErrors([
                'q' => ['El término debe contener al menos 2 caracteres.'],
            ]);
        }

        $results = $this->service->search($term);

        return $this->respondSuccess([
            'q'        => $term,
            'total'    => count($results),
            'results'  => $results,
        ], 'Búsqueda completada.');
    }

    public function missed(): ResponseInterface
    {
        $limit = (int) ($this->request->getGet('limit') ?? 100);

        return $this->respondSuccess([
            'items' => $this->service->listSearchMissed($limit),
        ], 'Búsquedas no encontradas obtenidas.');
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
