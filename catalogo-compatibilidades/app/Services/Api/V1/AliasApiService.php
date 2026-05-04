<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\AliasMotoModel;
use CodeIgniter\Database\BaseConnection;

class AliasApiService
{
    private AliasMotoModel $model;
    private BaseConnection $db;
    private string $timezone;

    public function __construct()
    {
        $this->model = new AliasMotoModel();
        $this->db = \Config\Database::connect();
        $this->timezone = config('App')->appTimezone ?: 'UTC';
    }

    public function list(array $query = []): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, min((int) ($query['per_page'] ?? 20), 100));
        $offset = ($page - 1) * $perPage;
        $sortBy = (string) ($query['sort_by'] ?? 'alias');
        $sortDir = strtoupper((string) ($query['sort_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $search = trim((string) ($query['q'] ?? ''));
        $motocicletaId = isset($query['motocicleta_id']) ? (int) $query['motocicleta_id'] : null;
        $marcaId = isset($query['marca_id']) ? (int) $query['marca_id'] : null;

        $sortMap = [
            'id' => 'a.id',
            'alias' => 'a.alias',
            'motocicleta_id' => 'a.motocicleta_id',
            'moto_modelo' => 'mo.modelo',
            'marca_nombre' => 'ma.nombre',
            'created_at' => 'a.created_at',
            'updated_at' => 'a.updated_at',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['alias'];

        $builder = $this->db->table('alias_motos a')
            ->select('a.id, a.motocicleta_id, a.alias, a.slug, a.created_at, a.updated_at, mo.modelo AS moto_modelo, ma.nombre AS marca_nombre')
            ->join('motocicletas mo', 'mo.id = a.motocicleta_id')
            ->join('marcas ma', 'ma.id = mo.marca_id');

        if ($search !== '') {
            $builder->groupStart()
                ->like('a.alias', $search)
                ->orLike('mo.modelo', $search)
                ->orLike('ma.nombre', $search)
                ->groupEnd();
        }
        if ($motocicletaId !== null && $motocicletaId > 0) {
            $builder->where('a.motocicleta_id', $motocicletaId);
        }
        if ($marcaId !== null && $marcaId > 0) {
            $builder->where('mo.marca_id', $marcaId);
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy($orderColumn, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(fn(array $row): array => $this->formatAliasRow($row), $items);

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
                    'motocicleta_id' => $motocicletaId,
                    'marca_id' => $marcaId,
                ],
                'timezone'  => $this->timezone,
            ],
        ];
    }

    public function create(array $data): int
    {
        $this->model->insert($data, true);
        return (int) $this->model->getInsertID();
    }

    public function find(int $id): ?array
    {
        return $this->detailById($id);
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

    public function detailById(int $id): ?array
    {
        $row = $this->db->table('alias_motos a')
            ->select('a.id, a.motocicleta_id, a.alias, a.slug, a.created_at, a.updated_at, mo.modelo AS moto_modelo, ma.nombre AS marca_nombre')
            ->join('motocicletas mo', 'mo.id = a.motocicleta_id')
            ->join('marcas ma', 'ma.id = mo.marca_id')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();

        return $row ? $this->formatAliasRow($row) : null;
    }

    private function formatAliasRow(array $row): array
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
