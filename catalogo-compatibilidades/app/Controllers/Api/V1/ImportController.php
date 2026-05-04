<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseApiController;
use App\Services\ImportService;
use CodeIgniter\HTTP\ResponseInterface;

class ImportController extends BaseApiController
{
    public function productos(): ResponseInterface
    {
        $file = $this->request->getFile('archivo');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->respondValidationErrors([
                'archivo' => ['No se recibiÃ³ un archivo vÃ¡lido.'],
            ]);
        }

        $tmpPath = WRITEPATH . 'uploads/tmp_' . $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/', basename($tmpPath));

        $nativeFile = [
            'name'     => $file->getClientName(),
            'tmp_name' => WRITEPATH . 'uploads/' . basename($tmpPath),
            'error'    => UPLOAD_ERR_OK,
            'size'     => $file->getSize(),
        ];

        $service = new ImportService();

        try {
            $result = $service->run($nativeFile);
        } finally {
            if (is_file($nativeFile['tmp_name'])) {
                @unlink($nativeFile['tmp_name']);
            }
        }

        if (!$result['ok']) {
            return $this->respondError($result['error'] ?: 'La importaciÃ³n fallÃ³.', null, ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $status = ResponseInterface::HTTP_CREATED;
        if (($result['errores'] ?? 0) > 0 && ($result['procesados'] ?? 0) > 0) {
            $status = ResponseInterface::HTTP_OK;
        }

        $message = $status === ResponseInterface::HTTP_OK
            ? 'ImportaciÃ³n finalizada con advertencias.'
            : 'ImportaciÃ³n completada.';

        return $this->respondSuccess([
            'job_id' => $result['job_id'],
            'estado' => $result['estado'] ?? 'finalizado',
            'total_items' => (int) ($result['total_items'] ?? 0),
            'procesados' => (int) ($result['procesados'] ?? 0),
            'errores' => (int) ($result['errores'] ?? 0),
        ], $message, $status);
    }
}
