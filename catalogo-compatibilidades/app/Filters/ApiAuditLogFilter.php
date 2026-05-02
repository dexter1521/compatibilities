<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\Api\V1\AuditLogService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuditLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $path = '/' . trim($request->getUri()->getPath(), '/');
        if (strpos($path, '/api/v1/') !== 0) {
            return;
        }

        $userId = null;
        if (isset($request->user) && is_array($request->user) && isset($request->user['sub'])) {
            $userId = (int) $request->user['sub'];
        }

        $userAgent = null;
        $uaObj = $request->getUserAgent();
        if ($uaObj !== null) {
            $userAgent = $uaObj->getAgentString();
        }

        (new AuditLogService())->log(
            $userId,
            $request->getMethod(),
            $path,
            $response->getStatusCode(),
            $request->getIPAddress(),
            $userAgent,
            null
        );
    }
}
