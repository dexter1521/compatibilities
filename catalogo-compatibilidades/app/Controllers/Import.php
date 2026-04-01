<?php

namespace App\Controllers;

use App\Services\ImportService;
use CodeIgniter\HTTP\ResponseInterface;

class Import extends BaseController
{
    // ── Página principal ───────────────────────────────────────

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
}
