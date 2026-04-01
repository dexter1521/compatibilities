<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

/**
 * ImportService
 *
 * Responsabilidades:
 *  1. Validar y mover el archivo subido
 *  2. Crear el registro import_job
 *  3. Leer el Excel/CSV fila por fila y crear import_items
 *  4. Por cada import_item: upsert en proveedores y productos
 *  5. Actualizar el import_job al finalizar
 *
 * El archivo Excel debe tener en la primera fila cualquier encabezado
 * o datos directamente. Se detectan las columnas por nombre (case-insensitive):
 *   proveedor | clave_proveedor | nombre
 * Si no hay encabezados nombrados, se asume: col A=proveedor, B=clave, C=nombre.
 */
class ImportService
{
    private \CodeIgniter\Database\BaseConnection $db;

    private const UPLOAD_DIR = WRITEPATH . 'uploads/';

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
    }

    /**
     * Punto de entrada principal.
     * Recibe el array de $_FILES['archivo'].
     * Devuelve ['ok' => bool, 'job_id' => int|null, 'error' => string].
     *
     * @param array{name:string,tmp_name:string,error:int,size:int} $file
     * @return array{ok:bool,job_id:int|null,error:string}
     */
    public function run(array $file): array
    {
        // ── Validar archivo ────────────────────────────────────
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'job_id' => null, 'error' => 'Error al subir el archivo (código ' . $file['error'] . ').'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return ['ok' => false, 'job_id' => null, 'error' => 'Formato no soportado. Solo se aceptan .xlsx, .xls o .csv.'];
        }

        $maxBytes = 20 * 1024 * 1024; // 20 MB
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'job_id' => null, 'error' => 'El archivo supera el límite de 20 MB.'];
        }

        // ── Mover a WRITEPATH/uploads/ ─────────────────────────
        $safeName   = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath   = self::UPLOAD_DIR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'job_id' => null, 'error' => 'No se pudo guardar el archivo en el servidor.'];
        }

        // ── Crear import_job ───────────────────────────────────
        $this->db->table('import_jobs')->insert([
            'archivo_nombre' => $file['name'],
            'estado'         => 'procesando',
            'iniciado_en'    => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $jobId = (int) $this->db->insertID();

        // ── Leer spreadsheet ───────────────────────────────────
        try {
            $reader = $this->buildReader($ext, $destPath);
            $sheet  = $reader->load($destPath)->getActiveSheet();
        } catch (\Throwable $e) {
            $this->failJob($jobId, 'No se pudo leer el archivo: ' . $e->getMessage());
            return ['ok' => false, 'job_id' => $jobId, 'error' => 'Archivo ilegible: ' . $e->getMessage()];
        }

        $rows    = $sheet->toArray(null, true, true, false);
        $headers = $this->detectHeaders($rows[0] ?? []);
        $data    = array_slice($rows, $headers['skip']);

        if (empty($data)) {
            $this->failJob($jobId, 'El archivo no tiene filas de datos.');
            return ['ok' => false, 'job_id' => $jobId, 'error' => 'El archivo está vacío o no tiene datos.'];
        }

        // ── Insertar import_items ──────────────────────────────
        $totalItems = 0;
        $startFila  = $headers['skip'] + 1;

        foreach ($data as $i => $row) {
            $proveedor     = trim((string)($row[$headers['proveedor']]     ?? ''));
            $claveProveedor = trim((string)($row[$headers['clave_proveedor']] ?? ''));
            $nombre        = trim((string)($row[$headers['nombre']]        ?? ''));

            if ($proveedor === '' && $claveProveedor === '' && $nombre === '') {
                continue; // fila vacía
            }

            $this->db->table('import_items')->insert([
                'import_job_id'   => $jobId,
                'fila_numero'     => $startFila + $i,
                'proveedor'       => $proveedor,
                'clave_proveedor' => $claveProveedor,
                'nombre'          => $nombre,
                'estado'          => 'pendiente',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            $totalItems++;
        }

        $this->db->table('import_jobs')
            ->where('id', $jobId)
            ->update(['total_items' => $totalItems, 'updated_at' => date('Y-m-d H:i:s')]);

        // ── Procesar cada item ─────────────────────────────────
        $procesados = 0;
        $errores    = 0;

        $items = $this->db->table('import_items')
            ->where('import_job_id', $jobId)
            ->where('estado', 'pendiente')
            ->get()
            ->getResultArray();

        foreach ($items as $item) {
            try {
                $this->processItem($item);
                $this->db->table('import_items')
                    ->where('id', $item['id'])
                    ->update(['estado' => 'procesado', 'updated_at' => date('Y-m-d H:i:s')]);
                $procesados++;
            } catch (\Throwable $e) {
                $this->db->table('import_items')
                    ->where('id', $item['id'])
                    ->update([
                        'estado'         => 'error',
                        'mensaje_error'  => mb_substr($e->getMessage(), 0, 500),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ]);
                $errores++;
            }
        }

        // ── Finalizar job ──────────────────────────────────────
        $this->db->table('import_jobs')
            ->where('id', $jobId)
            ->update([
                'estado'         => $errores > 0 && $procesados === 0 ? 'error' : 'finalizado',
                'procesados'     => $procesados,
                'errores'        => $errores,
                'finalizado_en'  => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

        return ['ok' => true, 'job_id' => $jobId, 'error' => ''];
    }

    // ── Privados ────────────────────────────────────────────────────────────

    /**
     * Upsert proveedor → upsert producto.
     * La clave de integración con MyBusiness es clave_proveedor.
     */
    private function processItem(array $item): void
    {
        $provNombre = $item['proveedor'] ?: 'Sin proveedor';
        $clave      = $item['clave_proveedor'];
        $nombre     = $item['nombre'];

        // Upsert proveedor
        $proveedor = $this->db->table('proveedores')
            ->where('nombre', $provNombre)
            ->get()->getRowArray();

        if ($proveedor) {
            $proveedorId = (int) $proveedor['id'];
        } else {
            $slug = $this->makeSlug($provNombre, 'proveedores');
            $this->db->table('proveedores')->insert([
                'nombre'     => $provNombre,
                'slug'       => $slug,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $proveedorId = (int) $this->db->insertID();
        }

        // Upsert producto por (proveedor_id, clave_proveedor)
        $producto = $this->db->table('productos')
            ->where('proveedor_id', $proveedorId)
            ->where('clave_proveedor', $clave)
            ->get()->getRowArray();

        if ($producto) {
            $this->db->table('productos')
                ->where('id', $producto['id'])
                ->update([
                    'nombre'     => $nombre,
                    'activo'     => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $slug = $this->makeSlug($clave . '-' . $nombre, 'productos');
            $this->db->table('productos')->insert([
                'proveedor_id'    => $proveedorId,
                'clave_proveedor' => $clave,
                'nombre'          => $nombre,
                'slug'            => $slug,
                'activo'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Detecta qué columna índice corresponde a cada campo.
     * Retorna ['proveedor' => idx, 'clave_proveedor' => idx, 'nombre' => idx, 'skip' => 0|1]
     */
    private function detectHeaders(array $firstRow): array
    {
        $map = ['proveedor' => 0, 'clave_proveedor' => 1, 'nombre' => 2, 'skip' => 0];

        $normalized = array_map('mb_strtolower', array_map('trim', $firstRow));

        foreach ($normalized as $i => $cell) {
            if (str_contains($cell, 'proveedor') && !str_contains($cell, 'clave')) {
                $map['proveedor'] = $i;
                $map['skip']      = 1;
            } elseif (str_contains($cell, 'clave')) {
                $map['clave_proveedor'] = $i;
                $map['skip']            = 1;
            } elseif (str_contains($cell, 'nombre') || str_contains($cell, 'descripcion') || str_contains($cell, 'descripción')) {
                $map['nombre'] = $i;
                $map['skip']   = 1;
            }
        }

        return $map;
    }

    private function buildReader(string $ext, string $path): IReader
    {
        if ($ext === 'csv') {
            $reader = IOFactory::createReader('Csv');
            $reader->setDelimiter(',');
            return $reader;
        }
        return IOFactory::createReaderForFile($path);
    }

    private function failJob(int $jobId, string $msg): void
    {
        $this->db->table('import_jobs')
            ->where('id', $jobId)
            ->update([
                'estado'        => 'error',
                'finalizado_en' => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
    }

    private function makeSlug(string $text, string $table): string
    {
        helper('url');
        $base = url_title(mb_strtolower($text), '-', true) ?: 'item';
        $slug = $base;
        $i    = 1;
        while ($this->db->table($table)->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
