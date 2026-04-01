<?php

namespace App\Models;

use CodeIgniter\Model;

class CompatibilidadModel extends Model
{
    protected $table         = 'compatibilidades';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pieza_maestra_id', 'motocicleta_id', 'confirmada', 'contador_confirmaciones'];
    protected $useTimestamps = true;

    public function getAllDetailed(): array
    {
        return $this->db->table('compatibilidades c')
            ->select('c.id, c.confirmada, c.contador_confirmaciones,
                      c.pieza_maestra_id, c.motocicleta_id,
                      pm.nombre AS pieza_nombre,
                      ma.nombre AS marca_nombre,
                      mo.modelo AS moto_modelo,
                      mo.anio_desde, mo.anio_hasta, mo.cilindrada')
            ->join('piezas_maestras pm', 'pm.id = c.pieza_maestra_id')
            ->join('motocicletas mo',    'mo.id = c.motocicleta_id')
            ->join('marcas ma',          'ma.id = mo.marca_id')
            ->orderBy('pm.nombre', 'ASC')
            ->orderBy('ma.nombre', 'ASC')
            ->orderBy('mo.modelo', 'ASC')
            ->get()
            ->getResultArray();
    }
}
