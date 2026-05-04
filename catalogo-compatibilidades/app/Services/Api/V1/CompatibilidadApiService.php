<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\CompatibilidadModel;
use CodeIgniter\Database\BaseConnection;

class CompatibilidadApiService
{
    private CompatibilidadModel $model;
    private BaseConnection $db;
    private string $timezone;

    public function __construct()
    {
        $this->model = new CompatibilidadModel();
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
        $piezaId = isset($query['pieza_maestra_id']) ? (int) $query['pieza_maestra_id'] : null;
        $motoId = isset($query['motocicleta_id']) ? (int) $query['motocicleta_id'] : null;
        $marcaId = isset($query['marca_id']) ? (int) $query['marca_id'] : null;
        $confirmada = array_key_exists('confirmada', $query) ? (int) $query['confirmada'] : null;

        $sortMap = [
            'id' => 'c.id',
            'pieza_nombre' => 'pm.nombre',
            'moto_modelo' => 'mo.modelo',
            'marca_nombre' => 'ma.nombre',
            'confirmada' => 'c.confirmada',
            'contador_confirmaciones' => 'c.contador_confirmaciones',
            'created_at' => 'c.created_at',
            'updated_at' => 'c.updated_at',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['id'];

        $builder = $this->db->table('compatibilidades c')
            ->select('c.id, c.confirmada, c.contador_confirmaciones,
                      c.pieza_maestra_id, c.motocicleta_id,
                      c.created_at, c.updated_at,
                      pm.nombre AS pieza_nombre,
                      ma.nombre AS marca_nombre,
                      mo.modelo AS moto_modelo,
                      mo.anio_desde, mo.anio_hasta, mo.cilindrada')
            ->join('piezas_maestras pm', 'pm.id = c.pieza_maestra_id')
            ->join('motocicletas mo', 'mo.id = c.motocicleta_id')
            ->join('marcas ma', 'ma.id = mo.marca_id');

        if ($search !== '') {
            $builder->groupStart()
                ->like('pm.nombre', $search)
                ->orLike('ma.nombre', $search)
                ->orLike('mo.modelo', $search)
                ->groupEnd();
        }
        if ($piezaId !== null && $piezaId > 0) {
            $builder->where('c.pieza_maestra_id', $piezaId);
        }
        if ($motoId !== null && $motoId > 0) {
            $builder->where('c.motocicleta_id', $motoId);
        }
        if ($marcaId !== null && $marcaId > 0) {
            $builder->where('ma.id', $marcaId);
        }
        if ($confirmada !== null && in_array($confirmada, [0, 1], true)) {
            $builder->where('c.confirmada', $confirmada);
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy($orderColumn, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(fn(array $row): array => $this->formatCompatibilidadRow($row), $items);

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
                    'pieza_maestra_id' => $piezaId,
                    'motocicleta_id' => $motoId,
                    'marca_id' => $marcaId,
                    'confirmada' => $confirmada,
                ],
                'timezone'  => $this->timezone,
            ],
        ];
    }

    public function find(int $id): ?array
    {
        return $this->detailById($id);
    }

    private function detailById(int $id): ?array
    {
        $row = $this->db->table('compatibilidades c')
            ->select('c.id, c.confirmada, c.contador_confirmaciones,
                      c.pieza_maestra_id, c.motocicleta_id,
                      c.created_at, c.updated_at,
                      pm.nombre AS pieza_nombre,
                      ma.nombre AS marca_nombre,
                      mo.modelo AS moto_modelo,
                      mo.anio_desde, mo.anio_hasta, mo.cilindrada')
            ->join('piezas_maestras pm', 'pm.id = c.pieza_maestra_id')
            ->join('motocicletas mo', 'mo.id = c.motocicleta_id')
            ->join('marcas ma', 'ma.id = mo.marca_id')
            ->where('c.id', $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return $this->formatCompatibilidadRow($row);
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

    private function formatCompatibilidadRow(array $row): array
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
