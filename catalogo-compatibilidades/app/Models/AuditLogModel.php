<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'metodo', 'ruta', 'status_code', 'ip', 'user_agent', 'payload', 'created_at'];
    protected $useTimestamps = false;
}
