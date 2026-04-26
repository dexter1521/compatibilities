<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\ProductoModel;
use CodeIgniter\Database\BaseConnection;

class ProductoService
{
    private BaseConnection $db;
    private ProductoModel $productoModel;
    private string $timezone;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->productoModel = new ProductoModel();
        $this->timezone = config('App')->appTimezone ?: 'UTC';
    }

    public function list(array $query = []): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, min((int) ($query['per_page'] ?? 20), 100));
        $offset = ($page - 1) * $perPage;
        $sortBy = (string) ($query['sort_by'] ?? 'id');
        $sortDir = strtoupper((string) ($query['sort_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $q = trim((string) ($query['q'] ?? ''));
        $proveedorId = isset($query['proveedor_id']) ? (int) $query['proveedor_id'] : null;
        $piezaMaestraId = isset($query['pieza_maestra_id']) ? (int) $query['pieza_maestra_id'] : null;
        $activo = isset($query['activo']) ? (int) $query['activo'] : null;
        $enrichEstado = isset($query['enrich_estado']) ? trim((string) $query['enrich_estado']) : null;

        $sortMap = [
            'id' => 'p.id',
            'nombre' => 'p.nombre',
            'clave_proveedor' => 'p.clave_proveedor',
            'created_at' => 'p.created_at',
            'updated_at' => 'p.updated_at',
            'proveedor_nombre' => 'pr.nombre',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['id'];

        $builder = $this->db->table('productos p')
            ->select('p.id, p.clave_proveedor, p.nombre, p.activo, p.enrich_estado, p.created_at, p.updated_at, p.proveedor_id, p.pieza_maestra_id, pr.nombre AS proveedor_nombre, pm.nombre AS pieza_nombre')
            ->join('proveedores pr', 'pr.id = p.proveedor_id', 'left')
            ->join('piezas_maestras pm', 'pm.id = p.pieza_maestra_id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.nombre', $q)
                ->orLike('p.clave_proveedor', $q)
                ->orLike('pr.nombre', $q)
                ->groupEnd();
        }
        if ($proveedorId !== null && $proveedorId > 0) {
            $builder->where('p.proveedor_id', $proveedorId);
        }
        if ($piezaMaestraId !== null && $piezaMaestraId > 0) {
            $builder->where('p.pieza_maestra_id', $piezaMaestraId);
        }
        if ($activo !== null && in_array($activo, [0, 1], true)) {
            $builder->where('p.activo', $activo);
        }
        if ($enrichEstado !== null && $enrichEstado !== '') {
            $builder->where('p.enrich_estado', $enrichEstado);
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy($orderColumn, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(fn(array $item): array => $this->formatProductoRow($item), $items);

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
                    'q' => $q !== '' ? $q : null,
                    'proveedor_id' => $proveedorId,
                    'pieza_maestra_id' => $piezaMaestraId,
                    'activo' => $activo,
                    'enrich_estado' => $enrichEstado !== '' ? $enrichEstado : null,
                ],
                'timezone'  => $this->timezone,
            ],
        ];
    }

    public function find(int $id): ?array
    {
        $row = $this->db->table('productos p')
            ->select('p.id, p.clave_proveedor, p.nombre, p.activo, p.enrich_estado, p.created_at, p.updated_at, p.proveedor_id, p.pieza_maestra_id, pr.nombre AS proveedor_nombre, pm.nombre AS pieza_nombre')
            ->join('proveedores pr', 'pr.id = p.proveedor_id', 'left')
            ->join('piezas_maestras pm', 'pm.id = p.pieza_maestra_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        return $row ? $this->formatProductoRow($row) : null;
    }

    public function create(array $payload): int
    {
        $this->productoModel->insert($payload, true);

        return (int) $this->productoModel->getInsertID();
    }

    public function update(int $id, array $payload): bool
    {
        return $this->productoModel->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->productoModel->delete($id);
    }

    public function existsByProveedorClave(int $proveedorId, string $claveProveedor, ?int $excludeId = null): bool
    {
        $q = $this->productoModel
            ->where('proveedor_id', $proveedorId)
            ->where('clave_proveedor', $claveProveedor);

        if ($excludeId !== null) {
            $q->where('id !=', $excludeId);
        }

        return $q->first() !== null;
    }

    public function makeUniqueSlug(string $baseText, ?int $excludeId = null): string
    {
        helper('url');

        $base = url_title(mb_strtolower(trim($baseText)), '-', true);
        if ($base === '') {
            $base = 'producto';
        }

        $slug = $base;
        $i = 1;

        while (true) {
            $q = $this->productoModel->where('slug', $slug);
            if ($excludeId !== null) {
                $q->where('id !=', $excludeId);
            }

            if ($q->first() === null) {
                return $slug;
            }

            $slug = $base . '-' . $i;
            $i++;
        }
    }

    private function formatProductoRow(array $row): array
    {
        $row['created_at'] = $this->formatDateTime($row['created_at'] ?? null);
        $row['updated_at'] = $this->formatDateTime($row['updated_at'] ?? null);

        return $row;
    }

    private function formatDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $dt = new \DateTimeImmutable($value, new \DateTimeZone($this->timezone));

        return $dt->format(\DateTimeInterface::ATOM);
    }
}
