<?php

namespace App\Controllers;

use App\Models\SearchModel;
use CodeIgniter\HTTP\ResponseInterface;

class Search extends BaseController
{
    private SearchModel $searchModel;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
    }

    /**
     * GET /buscador
     * Página completa del buscador.
     */
    public function index(): string
    {
        return view('buscador/index', [
            'title'     => 'Buscador — Compatibilidades',
            'pageTitle' => 'Buscador de Piezas',
        ]);
    }

    /**
     * GET /search?q=...
     * Endpoint HTMX: devuelve fragmento HTML con resultados.
     */
    public function results(): ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));

        if ($q === '' || mb_strlen($q) < 2) {
            return $this->response->setBody(view('buscador/_empty'));
        }

        $results = $this->searchModel->searchByTerm($q);

        if (empty($results)) {
            // Registrar el término no encontrado
            $this->searchModel->logMissedSearch($q);

            return $this->response->setBody(
                view('buscador/_no_results', ['q' => $q])
            );
        }

        return $this->response->setBody(
            view('buscador/_results', [
                'results' => $results,
                'q'       => $q,
            ])
        );
    }

    /**
     * POST /compatibilidades/{id}/confirm
     * HTMX: confirma que una compatibilidad funcionó.
     * Devuelve el botón reemplazado por un badge "Confirmado".
     */
    public function confirm(int $id): ResponseInterface
    {
        $db   = \Config\Database::connect();
        $row  = $db->table('compatibilidades')->where('id', $id)->get()->getRowArray();

        if (!$row) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setBody('<span class="badge badge-danger">No encontrado</span>');
        }

        $db->table('compatibilidades')->where('id', $id)->update([
            'confirmada'              => 1,
            'contador_confirmaciones' => (int) $row['contador_confirmaciones'] + 1,
            'updated_at'              => date('Y-m-d H:i:s'),
        ]);

        $nuevoContador = (int) $row['contador_confirmaciones'] + 1;

        $html = '<span class="compat-confirmed-badge" id="confirm-btn-' . $id . '">'
              . '<i class=\'bx bx-check-circle\'></i> Confirmado'
              . ' <span class="compat-confirmed-count">' . $nuevoContador . '</span>'
              . '</span>';

        return $this->response->setBody($html);
    }
}
