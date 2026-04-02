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
            'marcas'    => $this->searchModel->getMarcas(),
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
     * GET /compatibilidades/{id}/confirm
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

    /**
     * GET /cascada/modelos?marca_id=X
     * HTMX: devuelve <option> elements con los modelos de una marca.
     */
    public function cascadaModelos(): ResponseInterface
    {
        $marcaId = (int) $this->request->getGet('marca_id');

        if ($marcaId <= 0) {
            return $this->response->setBody('<option value="">— Selecciona un modelo —</option>');
        }

        $modelos = $this->searchModel->getModelosByMarca($marcaId);

        if (empty($modelos)) {
            return $this->response->setBody('<option value="">Sin modelos disponibles</option>');
        }

        $html = '<option value="">— Selecciona un modelo —</option>';
        foreach ($modelos as $m) {
            $label = $m['modelo'];
            $extra = [];
            if (!empty($m['cilindrada'])) {
                $extra[] = $m['cilindrada'];
            }
            if (!empty($m['anio_desde'])) {
                $anio = $m['anio_desde'];
                if (!empty($m['anio_hasta']) && $m['anio_hasta'] !== $m['anio_desde']) {
                    $anio .= '–' . $m['anio_hasta'];
                }
                $extra[] = $anio;
            }
            if (!empty($extra)) {
                $label .= ' (' . implode(', ', $extra) . ')';
            }
            $html .= '<option value="' . (int) $m['id'] . '">'
                   . esc($label)
                   . '</option>';
        }

        return $this->response->setBody($html);
    }

    /**
     * GET /search/por-moto?moto_id=X
     * HTMX: devuelve fragmento HTML con piezas compatibles con esa moto.
     */
    public function porMoto(): ResponseInterface
    {
        $motoId = (int) $this->request->getGet('moto_id');

        if ($motoId <= 0) {
            return $this->response->setBody(view('buscador/_empty'));
        }

        $results = $this->searchModel->searchByMoto($motoId);

        if (empty($results)) {
            // Obtener nombre de la moto para el mensaje
            $db   = \Config\Database::connect();
            $moto = $db->query(
                'SELECT CONCAT(ma.nombre, " ", mo.modelo) AS nombre
                 FROM motocicletas mo JOIN marcas ma ON ma.id = mo.marca_id
                 WHERE mo.id = ?',
                [$motoId]
            )->getRowArray();

            return $this->response->setBody(
                view('buscador/_no_results', ['q' => $moto['nombre'] ?? 'esta moto'])
            );
        }

        return $this->response->setBody(
            view('buscador/_results', [
                'results' => $results,
                'q'       => '',
            ])
        );
    }
}
