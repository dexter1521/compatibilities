<?php

namespace App\Controllers;

use App\Services\ImportService;
use CodeIgniter\HTTP\ResponseInterface;

class Import extends BaseController
{
    // ── Página principal ───────────────────────────────────────

    /**
     * GET /import
     * Muestra el formulario de importación y el historial de los últimos 20 jobs.
     */
    public function index(): string
    {
        $db   = \Config\Database::connect();
        $jobs = $db->table('import_jobs')
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return view('import/index', [
            'title'     => 'Importador — Compatibilidades',
            'pageTitle' => 'Importador Excel',
            'jobs'      => $jobs,
        ]);
    }

    // ── Subir y procesar ───────────────────────────────────────

    /**
     * POST /import/upload
     * Recibe el archivo subido, delega en ImportService::run() y redirige con flashdata.
     * El archivo se convierte de CIFile a array nativo antes de pasarse al servicio.
     */
    public function upload()
    {
        $file = $this->request->getFile('archivo');

        // CI4 CIFile → convertir a array nativo para el servicio
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'No se recibió ningún archivo válido.');
            return redirect()->to(site_url('/import'));
        }

        // Mover a tmp para pasar al servicio como array nativo
        $tmpPath = WRITEPATH . 'uploads/tmp_' . $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/', basename($tmpPath));

        $nativeFile = [
            'name'     => $file->getClientName(),
            'tmp_name' => WRITEPATH . 'uploads/' . basename($tmpPath),
            'error'    => UPLOAD_ERR_OK,
            'size'     => $file->getSize(),
        ];

        $service = new ImportService();
        $result  = $service->run($nativeFile);

        // Limpiar tmp
        if (file_exists($nativeFile['tmp_name'])) {
            @unlink($nativeFile['tmp_name']);
        }

        if ($result['ok']) {
            session()->setFlashdata('success', "Importación completada. Job #{$result['job_id']}.");
        } else {
            session()->setFlashdata('error', $result['error']);
        }

        return redirect()->to(site_url('/import'));
    }

    // ── Detalle de un job (HTMX partial) ──────────────────────

    /**
     * GET /import/job/{id}
     * Endpoint HTMX: devuelve el partial HTML con el detalle del job y sus items.
     *
     * @param int $id ID del import_job
     */
    public function jobDetail(int $id): ResponseInterface
    {
        $db  = \Config\Database::connect();
        $job = $db->table('import_jobs')->where('id', $id)->get()->getRowArray();

        if (!$job) {
            return $this->response->setStatusCode(404)->setBody('<p class="text-danger">Job no encontrado.</p>');
        }

        $items = $db->table('import_items')
            ->where('import_job_id', $id)
            ->orderBy('fila_numero', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setBody(
            view('import/_job_detail', ['job' => $job, 'items' => $items])
        );
    }

    // ── Pendientes de enriquecimiento ──────────────────────

    /**
     * GET /import/pendientes
     * Endpoint HTMX: devuelve tabla de productos que no pudieron enriquecerse.
     */
    public function pendientes(): ResponseInterface
    {
        $db = \Config\Database::connect();

        $rows = $db->table('productos p')
            ->select('p.id, p.nombre, p.clave_proveedor, p.enrich_estado, pr.nombre AS proveedor')
            ->join('proveedores pr', 'pr.id = p.proveedor_id', 'left')
            ->whereIn('p.enrich_estado', ['sin_tipo', 'sin_moto', 'sin_ambos'])
            ->orWhere('p.enrich_estado IS NULL')
            ->orderBy('p.enrich_estado')
            ->orderBy('p.nombre')
            ->get()->getResultArray();

        return $this->response->setBody(
            view('import/_pendientes', ['pendientes' => $rows])
        );
    }

    /**
     * POST /import/reenrich
     * Reintenta el enriquecimiento de todos los productos pendientes.
     * Devuelve JSON con contadores para HTMX.
     */
    public function reenrich(): ResponseInterface
    {
        $service = new ImportService();
        $result  = $service->reenrichPendientes();

        session()->setFlashdata(
            'success',
            "Reintento completado: {$result['ok']} enriquecidos, {$result['pendientes']} aún pendientes."
        );

        return $this->response
            ->setHeader('HX-Redirect', site_url('/import'))
            ->setBody('');
