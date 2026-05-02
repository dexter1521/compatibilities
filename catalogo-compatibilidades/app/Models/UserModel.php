<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'nombre', 'email', 'password_hash', 'activo'];
    protected $useTimestamps = true;
}
