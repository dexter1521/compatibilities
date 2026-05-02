<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AliasMotoModel extends Model
{
    protected $table            = 'alias_motos';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'motocicleta_id',
        'alias',
        'slug',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
