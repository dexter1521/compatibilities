<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table = 'refresh_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token_hash', 'expires_at', 'revoked_at'];
    protected $useTimestamps = true;
}
