<?php

namespace App\Models;

use CodeIgniter\Model;

class MarcaModel extends Model
{
    protected $table         = 'marcas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'slug', 'activo'];
    protected $useTimestamps = true;
}
