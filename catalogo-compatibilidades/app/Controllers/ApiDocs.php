<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class ApiDocs extends BaseController
{
    public function index(): string
    {
        return view('docs/api');
    }

    public function spec(string $filename = 'openapi.yaml'): ResponseInterface
    {
        if (!str_ends_with((string) $filename, '.yaml') && !str_ends_with((string) $filename, '.yml')) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('Especificacion no encontrada');
        }

        $path = ROOTPATH . 'openapi/' . $filename;

        if (!is_file($path)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('openapi.yaml no encontrado');
        }

        $content = (string) file_get_contents($path);

        return $this->response
            ->setHeader('Content-Type', 'application/yaml; charset=UTF-8')
            ->setBody($content);
    }
}

