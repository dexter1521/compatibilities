<?php

namespace App\Models;

use CodeIgniter\Model;

class MotoModel extends Model
{
    protected $table         = 'motocicletas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['marca_id', 'modelo', 'anio_desde', 'anio_hasta', 'cilindrada', 'slug'];
    protected $useTimestamps = true;

    public function getAllWithMarca(): array
    {
        return $this->db->table('motocicletas m')
            ->select('m.id, m.modelo, m.anio_desde, m.anio_hasta, m.cilindrada, m.marca_id, ma.nombre AS marca_nombre')
            ->join('marcas ma', 'ma.id = m.marca_id')
            ->orderBy('ma.nombre', 'ASC')
            ->orderBy('m.modelo', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getWithMarca(int $id): ?array
    {
        return $this->db->table('motocicletas m')
            ->select('m.*, ma.nombre AS marca_nombre')
            ->join('marcas ma', 'ma.id = m.marca_id')
            ->where('m.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }
}
