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

    public function spec(): ResponseInterface
    {
        $path = ROOTPATH . 'docs/openapi.yaml';

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
