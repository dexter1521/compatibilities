<?php

namespace App\Models;

use CodeIgniter\Model;

class PiezaMaestraModel extends Model
{
    protected $table         = 'piezas_maestras';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'slug'];
    protected $useTimestamps = true;
}
