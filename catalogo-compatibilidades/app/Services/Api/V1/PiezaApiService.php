<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\PiezaMaestraModel;

class PiezaApiService
{
    private PiezaMaestraModel $model;

    public function __construct()
    {
        $this->model = new PiezaMaestraModel();
    }

    public function list(): array
    {
        return $this->model->orderBy('nombre', 'ASC')->findAll();
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ?: null;
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

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        $q = $this->model->where('LOWER(nombre)', mb_strtolower(trim($nombre)));
        if ($excludeId !== null) {
            $q->where('id !=', $excludeId);
        }
        return $q->first() !== null;
    }

    public function makeSlug(string $nombre, ?int $excludeId = null): string
    {
        helper('url');

        $base = url_title(mb_strtolower(trim($nombre)), '-', true) ?: 'pieza';
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
