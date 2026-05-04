<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\MotoModel;
use CodeIgniter\Database\BaseConnection;

class MotoApiService
{
    private MotoModel $model;
    private BaseConnection $db;
    private string $timezone;

    public function __construct()
    {
        $this->model = new MotoModel();
        $this->db = \Config\Database::connect();
        $this->timezone = config('App')->appTimezone ?: 'UTC';
    }

    public function list(array $query = []): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, min((int) ($query['per_page'] ?? 20), 100));
        $offset = ($page - 1) * $perPage;
        $sortBy = (string) ($query['sort_by'] ?? 'id');
        $sortDir = strtoupper((string) ($query['sort_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $search = trim((string) ($query['q'] ?? ''));
        $marcaId = isset($query['marca_id']) ? (int) $query['marca_id'] : null;

        $sortMap = [
            'id' => 'm.id',
            'modelo' => 'm.modelo',
            'marca_id' => 'm.marca_id',
            'marca_nombre' => 'ma.nombre',
            'anio_desde' => 'm.anio_desde',
            'anio_hasta' => 'm.anio_hasta',
            'cilindrada' => 'm.cilindrada',
            'created_at' => 'm.created_at',
            'updated_at' => 'm.updated_at',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['id'];

        $builder = $this->db->table('motocicletas m')
            ->select('m.id, m.modelo, m.anio_desde, m.anio_hasta, m.cilindrada, m.marca_id, m.created_at, m.updated_at, ma.nombre AS marca_nombre')
            ->join('marcas ma', 'ma.id = m.marca_id', 'left');

        if ($search !== '') {
            $builder->groupStart()
                ->like('m.modelo', $search)
                ->orLike('ma.nombre', $search)
                ->groupEnd();
        }

        if ($marcaId !== null && $marcaId > 0) {
            $builder->where('m.marca_id', $marcaId);
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy($orderColumn, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(fn(array $row): array => $this->formatMotoRow($row), $items);

        return [
            'items' => $items,
            'meta'  => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => (int) ceil($total / $perPage),
                'sort_by'   => $sortBy,
                'sort_dir'  => strtolower($sortDir),
                'filters'   => [
                    'q' => $search !== '' ? $search : null,
                    'marca_id' => $marcaId,
                ],
                'timezone'  => $this->timezone,
            ],
        ];
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

    private function formatMotoRow(array $row): array
    {
        $row['created_at'] = $this->formatDateTime($row['created_at'] ?? null);
        $row['updated_at'] = $this->formatDateTime($row['updated_at'] ?? null);

        return $row;
    }

    private function formatDateTime(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (new \DateTimeImmutable($value, new \DateTimeZone($this->timezone)))->format(\DateTimeInterface::ATOM);
    }
}
