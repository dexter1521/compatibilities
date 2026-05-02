<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'slug'];
    protected $useTimestamps = true;
}
