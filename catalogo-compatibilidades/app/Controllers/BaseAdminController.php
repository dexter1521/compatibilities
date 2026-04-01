<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Base para controladores CRUD del área de administración.
 * Provee helpers de slug único y redirect HTMX.
 */
abstract class BaseAdminController extends BaseController
{
    /**
     * Genera un slug único buscando colisiones en la tabla indicada.
     */
    protected function uniqueSlug(string $text, string $table, int $excludeId = 0): string
    {
        helper('url');
        $base = url_title(mb_strtolower($text), '-', true);
        if ($base === '') {
            $base = 'item';
        }
        $slug = $base;
        $db   = \Config\Database::connect();
        $i    = 1;

        while (true) {
            $q = $db->table($table)->where('slug', $slug);
            if ($excludeId > 0) {
                $q->where('id !=', $excludeId);
            }
            if ($q->countAllResults() === 0) {
                break;
            }
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Respuesta HTMX que dispara una redirección completa de página.
     * Úsala en store() / update() para cerrar el modal y refrescar la lista.
     */
    protected function htmxRedirect(string $url): ResponseInterface
    {
        return $this->response
            ->setHeader('HX-Redirect', $url)
            ->setStatusCode(200)
            ->setBody('');
    }
}
