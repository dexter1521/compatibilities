<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\MotoModel;

class MotoApiService
{
    private MotoModel $model;

    public function __construct()
    {
        $this->model = new MotoModel();
    }

    public function list(): array
    {
        return $this->model->getAllWithMarca();
    }

    public function find(int $id): ?array
    {
        return $this->model->getWithMarca($id);
    }

    public function create(array $data): int
    {
        $this->model->insert($data, true);

        return (int) $this->model->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    public function existsByMarcaModelo(int $marcaId, string $modelo, ?int $excludeId = null): bool
    {
        $q = $this->model->where('marca_id', $marcaId)->where('LOWER(modelo)', mb_strtolower(trim($modelo)));
        if ($excludeId !== null) {
            $q->where('id !=', $excludeId);
        }

        return $q->first() !== null;
    }

    public function makeSlug(string $modelo, int $marcaId, ?int $excludeId = null): string
    {
        helper('url');

        $base = url_title(mb_strtolower(trim($marcaId . '-' . $modelo)), '-', true) ?: 'moto';
        $slug = $base;
        $i = 1;

        while (true) {
            $q = $this->model->where('slug', $slug);
            if ($excludeId !== null) {
                $q->where('id !=', $excludeId);
            }
            if ($q->first() === null) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
