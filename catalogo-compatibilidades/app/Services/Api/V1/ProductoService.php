<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\ProductoModel;
use CodeIgniter\Database\BaseConnection;

class ProductoService
{
    private BaseConnection $db;
    private ProductoModel $productoModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->productoModel = new ProductoModel();
    }

    public function list(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('productos p')
            ->select('p.id, p.clave_proveedor, p.nombre, p.activo, p.enrich_estado, p.created_at, p.updated_at, p.proveedor_id, p.pieza_maestra_id, pr.nombre AS proveedor_nombre, pm.nombre AS pieza_nombre')
            ->join('proveedores pr', 'pr.id = p.proveedor_id', 'left')
            ->join('piezas_maestras pm', 'pm.id = p.pieza_maestra_id', 'left');

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->orderBy('p.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'items' => $items,
            'meta'  => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => (int) ceil($total / $perPage),
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

        return $row ?: null;
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
}
