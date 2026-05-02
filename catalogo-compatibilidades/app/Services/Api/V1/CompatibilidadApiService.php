<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\CompatibilidadModel;

class CompatibilidadApiService
{
    private CompatibilidadModel $model;

    public function __construct()
    {
        $this->model = new CompatibilidadModel();
    }

    public function list(): array
    {
        return $this->model->getAllDetailed();
    }

    public function find(int $id): ?array
    {
        $row = $this->model->find($id);
        if (!$row) {
            return null;
        }

        return $this->model->getAllDetailed() ? $this->detailById($id) : $row;
    }

    private function detailById(int $id): ?array
    {
        $rows = $this->model->getAllDetailed();
        foreach ($rows as $row) {
            if ((int) $row['id'] === $id) {
                return $row;
            }
        }
        return null;
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

    public function existsPar(int $piezaId, int $motoId, ?int $excludeId = null): bool
    {
        $q = $this->model->where('pieza_maestra_id', $piezaId)->where('motocicleta_id', $motoId);
        if ($excludeId !== null) {
            $q->where('id !=', $excludeId);
        }

        return $q->first() !== null;
    }
}
