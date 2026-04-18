<?php

namespace App\Controllers;

use App\Services\ImportService;
use CodeIgniter\HTTP\ResponseInterface;

class Import extends BaseController
{
    // ── Página principal ──────────────────────────────────────

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

    // ── Subir y procesar ──────────────────────────────────────

    /**
     * POST /import/upload
     * Recibe el archivo subido, delega en ImportService::run() y redirige con flashdata.
     */
    public function upload()
    {
        $file = $this->request->getFile('archivo');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'No se recibió ningún archivo válido.');
            return redirect()->to(site_url('/import'));
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
        $result  = $service->run($nativeFile);

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

    // ── Detalle de un job (HTMX partial) ─────────────────────

    /**
     * GET /import/job/{id}
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

    // ── Pendientes de enriquecimiento ─────────────────────────

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
            ->get()
            ->getResultArray();

        return $this->response->setBody(
            view('import/_pendientes', ['pendientes' => $rows])
        );
    }

    // ── Reintento de enriquecimiento ──────────────────────────

    /**
     * POST /import/reenrich
     * Reintenta el enriquecimiento de todos los productos pendientes.
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
    }

    /**
     * POST /import/detectar-modelos
     * Equivalente web de `php spark detectar:modelos`.
     * Escanea los nombres de productos para detectar códigos de moto,
     * genera aliases automáticos para las motos existentes y guarda
     * los modelos desconocidos en modelos_detectados_raw.
     */
    public function detectarModelos(): ResponseInterface
    {
        $db = \Config\Database::connect();

        // Prefijos canónicos de modelos de moto
        $prefijos = [
            'ft','dm','dt','ds','at','ws','rc','ns','rs','gn','gs',
            'cg','fz','bws','en','ybr','cs','dsg','gts','xft','dsr',
            'rt','sz','gl',
        ];

        $productos = $db->table('productos')->select('id, nombre')->get()->getResultArray();

        $aliasInserted = [];
        $aliasCreados  = 0;
        $modelosNuevos = 0;
        $rawInserted   = [];

        foreach ($productos as $p) {
            $desc = mb_strtolower($p['nombre']);
            $desc = str_replace(['-', '_', '.', ';'], ' ', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc);

            preg_match_all('/\b([a-z]{1,4})\s?-?\s?(\d{2,3})\b/i', $desc, $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $prefijo = strtolower(trim($m[1]));
                $numero  = trim($m[2]);
                $modelo  = strtoupper($prefijo . $numero);

                if (!in_array($prefijo, $prefijos, true)) {
                    continue;
                }

                // Buscar moto (exacta primero, luego LIKE)
                $moto = $db->table('motocicletas')->select('id')->where('UPPER(modelo)', $modelo)->get()->getRowArray();
                if (!$moto) {
                    $moto = $db->table('motocicletas')->select('id')->like('modelo', $modelo)->limit(1)->get()->getRowArray();
                }

                if (!$moto) {
                    if (!in_array($modelo, $rawInserted, true)) {
                        $db->table('modelos_detectados_raw')->insert([
                            'texto_detectado' => $modelo,
                            'nombre_producto' => $p['nombre'],
                        ]);
                        $rawInserted[] = $modelo;
                        $modelosNuevos++;
                    }
                    continue;
                }

                $motoId = (int) $moto['id'];

                // Generar 3 variantes de alias
                preg_match('/^([A-Z]+)(\d+)$/', $modelo, $parts);
                if (!$parts) continue;

                foreach ([$modelo, "{$parts[1]} {$parts[2]}", "{$parts[1]}-{$parts[2]}"] as $alias) {
                    $cacheKey = "{$motoId}:{$alias}";
                    if (isset($aliasInserted[$cacheKey])) continue;
                    $aliasInserted[$cacheKey] = true;

                    if ($db->table('alias_motos')->where('UPPER(alias)', strtoupper($alias))->countAllResults() > 0) continue;

                    $slug = strtolower(str_replace(' ', '-', $alias));
                    $base = $slug; $i = 1;
                    while ($db->table('alias_motos')->where('slug', $slug)->countAllResults() > 0) {
                        $slug = $base . '-' . $i++;
                    }
                    $db->table('alias_motos')->insert([
                        'motocicleta_id' => $motoId,
                        'alias'          => strtoupper($alias),
                        'slug'           => $slug,
                    ]);
                    $aliasCreados++;
                }
            }
        }

        session()->setFlashdata(
            'success',
            "Detección completada: {$aliasCreados} aliases creados, {$modelosNuevos} modelos nuevos en revisión."
        );

        return $this->response
            ->setHeader('HX-Redirect', site_url('/import'))
            ->setBody('');
    }
}
