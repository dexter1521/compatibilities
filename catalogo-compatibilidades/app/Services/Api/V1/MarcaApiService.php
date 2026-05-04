<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\MarcaModel;
use CodeIgniter\Database\BaseConnection;

class MarcaApiService
{
    private MarcaModel $model;
    private BaseConnection $db;
    private string $timezone;

    public function __construct()
    {
        $this->model = new MarcaModel();
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
        $activo = array_key_exists('activo', $query) ? (int) $query['activo'] : null;

        $sortMap = [
            'id' => 'm.id',
            'nombre' => 'm.nombre',
            'slug' => 'm.slug',
            'activo' => 'm.activo',
            'created_at' => 'm.created_at',
            'updated_at' => 'm.updated_at',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['id'];

        $builder = $this->db->table('marcas m')
            ->select('m.id, m.nombre, m.slug, m.activo, m.created_at, m.updated_at');

        if ($search !== '') {
            $builder->groupStart()
                ->like('m.nombre', $search)
                ->orLike('m.slug', $search)
                ->groupEnd();
        }
        if ($activo !== null && in_array($activo, [0, 1], true)) {
            $builder->where('m.activo', $activo);
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy($orderColumn, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(fn(array $row): array => $this->formatMarcaRow($row), $items);

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
                    'activo' => $activo,
                ],
                'timezone'  => $this->timezone,
            ],
        ];
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

        $base = url_title(mb_strtolower(trim($nombre)), '-', true) ?: 'marca';
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

    private function formatMarcaRow(array $row): array
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
