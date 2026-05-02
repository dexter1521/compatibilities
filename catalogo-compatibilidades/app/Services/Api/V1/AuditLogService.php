<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\AuditLogModel;

class AuditLogService
{
    private AuditLogModel $model;

    public function __construct()
    {
        $this->model = new AuditLogModel();
    }

    public function log(?int $userId, string $method, string $path, int $statusCode, ?string $ip, ?string $userAgent, ?array $payload = null): void
    {
        $this->model->insert([
            'user_id' => $userId,
            'metodo' => strtoupper($method),
            'ruta' => $path,
            'status_code' => $statusCode,
            'ip' => $ip,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
