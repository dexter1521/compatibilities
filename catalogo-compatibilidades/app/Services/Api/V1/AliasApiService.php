<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\AliasMotoModel;

class AliasApiService
{
    private AliasMotoModel $model;

    public function __construct()
    {
        $this->model = new AliasMotoModel();
    }

    public function list(): array
    {
        return $this->model->select('alias_motos.id, alias_motos.motocicleta_id, alias_motos.alias, alias_motos.slug, alias_motos.created_at, alias_motos.updated_at, mo.modelo AS moto_modelo, ma.nombre AS marca_nombre')
            ->join('motocicletas mo', 'mo.id = alias_motos.motocicleta_id')
            ->join('marcas ma', 'ma.id = mo.marca_id')
            ->orderBy('alias_motos.alias', 'ASC')
            ->findAll();
    }

    public function create(array $data): int
    {
        $this->model->insert($data, true);
        return (int) $this->model->getInsertID();
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function existsByAlias(string $alias, ?int $excludeId = null): bool
    {
        $q = $this->model->where('UPPER(alias)', mb_strtoupper(trim($alias)));
        if ($excludeId !== null) {
            $q->where('id !=', $excludeId);
        }
        return $q->first() !== null;
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    public function makeSlug(string $alias, ?int $excludeId = null): string
    {
        helper('url');

        $base = url_title(mb_strtolower(trim($alias)), '-', true) ?: 'alias';
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
