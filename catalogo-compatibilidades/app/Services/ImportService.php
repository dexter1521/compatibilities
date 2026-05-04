<?php

declare(strict_types=1);

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
    private string $timezone;

    private array $aliasCache       = [];
    private array $piezaMaestraCache = [];
    private array $motoLabelCache    = []; // motoId → 'MARCA-MODELO'

    private array $mapTipos = [
        'Filtro de Aceite'      => ['FILTRO DE ACEITE'],
        'Filtro de Aire'        => ['FILTRO DE AIRE', 'FILTRO AIRE'],
        'Balata Delantera'      => ['BALATA DELANTERA', 'PASTILLA DELANTERA'],
        'Balata Trasera'        => ['BALATA TRASERA', 'PASTILLA TRASERA'],
        'Balata'                => ['PASTILLA DE FRENO', 'BALATA'],
        'Bujía'                 => ['BUJIA', 'SPARK PLUG'],
        'Kit de Arrastre'       => ['KIT DE ARRASTRE', 'KIT ARRASTRE'],
        'Llanta Delantera'      => ['LLANTA DELANTERA'],
        'Llanta Trasera'        => ['LLANTA TRASERA'],
        'Llanta'                => ['LLANTA', 'NEUMATICO'],
        'Cadena de Transmisión' => ['CADENA'],
        'Corona'                => ['CORONA'],
        'Catarina'              => ['CATARINA'],
        'Clutch'                => ['CLUTCH', 'EMBRAGUE'],
        'Amortiguador'          => ['AMORTIGUADOR'],
        'Carburador'            => ['CARBURADOR'],
        'Batería'               => ['BATERIA'],
        'Aceite de Motor'       => ['ACEITE'],
    ];

    private const UPLOAD_DIR = WRITEPATH . 'uploads/';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->timezone = config('App')->appTimezone ?: 'UTC';

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
    }

    /**
     * Punto de entrada principal.
     * Recibe el array de $_FILES['archivo'].
     * Devuelve ['ok' => bool, 'job_id' => int|null, 'estado' => string|null, 'total_items' => int|null, 'procesados' => int|null, 'errores' => int|null, 'error' => string].
     *
     * @param array{name:string,tmp_name:string,error:int,size:int} $file
     * @return array{ok:bool,job_id:int|null,estado:string|null,total_items:int|null,procesados:int|null,errores:int|null,error:string}
     */
    public function run(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->buildRunResult(false, null, 'Error al subir el archivo (código ' . $file['error'] . ').');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return $this->buildRunResult(false, null, 'Formato no soportado. Solo se aceptan .xlsx, .xls o .csv.');
        }

        $maxBytes = 20 * 1024 * 1024; // 20 MB
        if ($file['size'] > $maxBytes) {
            return $this->buildRunResult(false, null, 'El archivo supera el límite de 20 MB.');
        }

        $safeName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = self::UPLOAD_DIR . $safeName;

        if (!rename($file['tmp_name'], $destPath)) {
            return $this->buildRunResult(false, null, 'No se pudo guardar el archivo en el servidor.');
        }

        $this->db->table('import_jobs')->insert([
            'archivo_nombre' => $file['name'],
            'estado'         => 'procesando',
            'iniciado_en'    => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $jobId = (int) $this->db->insertID();

        try {
            $reader = $this->buildReader($ext, $destPath);
            $sheet  = $reader->load($destPath)->getActiveSheet();
        } catch (\Throwable $e) {
            $this->failJob($jobId, 'No se pudo leer el archivo: ' . $e->getMessage());
            return $this->buildRunResult(false, $jobId, 'Archivo ilegible: ' . $e->getMessage());
        }

        $rows    = $sheet->toArray(null, true, true, false);
        $headers = $this->detectHeaders($rows[0] ?? []);
        $data    = array_slice($rows, $headers['skip']);

        if (empty($data)) {
            $this->failJob($jobId, 'El archivo no tiene filas de datos.');
            return $this->buildRunResult(false, $jobId, 'El archivo está vacío o no tiene datos.');
        }

        $totalItems = 0;
        $startFila  = $headers['skip'] + 1;

        foreach ($data as $i => $row) {
            $proveedor      = $headers['proveedor'] !== null
                ? trim((string)($row[$headers['proveedor']] ?? ''))
                : '';
            $claveProveedor = trim((string)($row[$headers['clave_proveedor']] ?? ''));
            $nombre         = trim((string)($row[$headers['nombre']]          ?? ''));
            $marca          = $headers['marca'] !== null
                ? trim((string)($row[$headers['marca']] ?? ''))
                : '';
            $linea          = $headers['linea'] !== null
                ? trim((string)($row[$headers['linea']] ?? ''))
                : '';

            if ($claveProveedor === '' && $nombre === '') {
                continue;
            }

            $this->db->table('import_items')->insert([
                'import_job_id'   => $jobId,
                'fila_numero'     => $startFila + $i,
                'proveedor'       => $proveedor,
                'clave_proveedor' => $claveProveedor,
                'nombre'          => $nombre,
                'marca'           => $marca !== '' ? $marca : null,
                'linea'           => $linea !== '' ? $linea : null,
                'estado'          => 'pendiente',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            $totalItems++;
        }

        $this->db->table('import_jobs')
            ->where('id', $jobId)
            ->update(['total_items' => $totalItems, 'updated_at' => date('Y-m-d H:i:s')]);

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
                        'estado'        => 'error',
                        'mensaje_error' => mb_substr($e->getMessage(), 0, 500),
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ]);
                $errores++;
            }
        }

        $estado = $this->buildImportState($procesados, $errores);

        $this->db->table('import_jobs')
            ->where('id', $jobId)
            ->update([
                'estado'         => $estado,
                'procesados'     => $procesados,
                'errores'        => $errores,
                'finalizado_en'  => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

        return $this->buildRunResult(true, $jobId, '', [
            'total_items' => $totalItems,
            'procesados'  => $procesados,
            'errores'     => $errores,
            'estado'      => $estado,
        ]);
    }

    /**
     * Upsert proveedor → upsert producto.
     * La clave de integración con MyBusiness es clave_proveedor.
     */
    private function processItem(array $item): void
    {
        $provNombre = ($item['proveedor'] ?? '') !== '' ? $item['proveedor'] : 'Sin proveedor';
        $clave      = $item['clave_proveedor'];
        $nombre     = $item['nombre'];
        $marca      = $item['marca']  ?? null;
        $linea      = $item['linea']  ?? null;

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
            $productoId = (int) $producto['id'];
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
            $productoId = (int) $this->db->insertID();
        }

        $this->enrichProducto($productoId, $nombre, $marca, $linea);
    }

    /**
     * Detecta qué columna índice corresponde a cada campo.
     * Soporta encabezados clásicos y los del Excel de Shark Motors:
     *   articulo → clave_proveedor, descrip → nombre, marca → marca, linea → linea
     *
     * @return array{proveedor:int|null,clave_proveedor:int,nombre:int,marca:int|null,linea:int|null,skip:int}
     */
    private function detectHeaders(array $firstRow): array
    {
        $map = [
            'proveedor'      => null,  // null = sin columna → usar 'Sin proveedor'
            'clave_proveedor' => 0,
            'nombre'         => 1,
            'marca'          => null,
            'linea'          => null,
            'skip'           => 0,
        ];

        $normalized = array_map('mb_strtolower', array_map('trim', $firstRow));
        $hasHeader  = false;

        foreach ($normalized as $i => $cell) {
            if ($cell === '') {
                continue;
            }
            // proveedor
            if (str_contains($cell, 'proveedor') && !str_contains($cell, 'clave')) {
                $map['proveedor'] = $i;
                $hasHeader = true;
            }
            // clave_proveedor / articulo / sku
            elseif (in_array($cell, ['articulo', 'artículo', 'sku', 'codigo', 'código', 'clave_proveedor', 'clave'], true)
                || str_contains($cell, 'articul')
                || (str_contains($cell, 'clave') && !str_contains($cell, 'prov'))) {
                $map['clave_proveedor'] = $i;
                $hasHeader = true;
            }
            // nombre / descrip
            elseif (in_array($cell, ['descrip', 'descripcion', 'descripción', 'nombre', 'producto', 'descripci'], true)
                || str_contains($cell, 'descri')
                || str_contains($cell, 'nombre')) {
                $map['nombre'] = $i;
                $hasHeader = true;
            }
            // marca
            elseif ($cell === 'marca' || str_contains($cell, 'marca')) {
                $map['marca'] = $i;
                $hasHeader    = true;
            }
            // linea / tipo
            elseif (in_array($cell, ['linea', 'línea', 'tipo', 'categoria', 'categoría', 'familia'], true)
                || str_contains($cell, 'linea')
                || str_contains($cell, 'línea')) {
                $map['linea'] = $i;
                $hasHeader    = true;
            }
        }

        if ($hasHeader) {
            $map['skip'] = 1;
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

    private function buildRunResult(bool $ok, ?int $jobId, string $error, array $extra = []): array
    {
        $job = $this->getImportJobSummary($jobId);

        return [
            'ok'         => $ok,
            'job_id'     => $jobId,
            'estado'     => $extra['estado']     ?? $job['estado'],
            'total_items'=> $extra['total_items'] ?? $job['total_items'],
            'procesados' => $extra['procesados']  ?? $job['procesados'],
            'errores'    => $extra['errores']     ?? $job['errores'],
            'error'      => $error,
        ];
    }

    private function getImportJobSummary(?int $jobId): array
    {
        if ($jobId === null) {
            return [
                'estado' => null,
                'total_items' => null,
                'procesados' => null,
                'errores' => null,
            ];
        }

        $row = $this->db->table('import_jobs')
            ->select('estado, total_items, procesados, errores')
            ->where('id', $jobId)
            ->get()
            ->getRowArray();

        if (!$row) {
            return [
                'estado' => null,
                'total_items' => null,
                'procesados' => null,
                'errores' => null,
            ];
        }

        return [
            'estado' => $row['estado'] ?? null,
            'total_items' => isset($row['total_items']) ? (int) $row['total_items'] : null,
            'procesados' => isset($row['procesados']) ? (int) $row['procesados'] : null,
            'errores' => isset($row['errores']) ? (int) $row['errores'] : null,
        ];
    }

    private function buildImportState(int $procesados, int $errores): string
    {
        if ($errores > 0 && $procesados === 0) {
            return 'error';
        }

        if ($errores > 0) {
            return 'finalizado_con_errores';
        }

        return 'finalizado';
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
        // Truncar antes de generar para que el slug nunca exceda el constraint
        $base = url_title(mb_strtolower(mb_substr($text, 0, 480)), '-', true) ?: 'item';
        $slug = $base;
        $i    = 1;
        while ($this->db->table($table)->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    // ── Enriquecimiento automático ──────────────────────────────────────────

    /**
     * @param string|null $marcaNombre  Nombre de la marca (del Excel), usado para filtrar motos.
     * @param string|null $lineaNombre  Nombre de la línea (del Excel), usado como tipo directo.
     */
    private function enrichProducto(int $productoId, string $descripcion, ?string $marcaNombre = null, ?string $lineaNombre = null): void
    {
        $desc = $this->normalize($descripcion);

        // 1. Tipo: usar la columna linea si viene; si no, detectar del texto
        $tipo = null;
        if ($lineaNombre !== null && $lineaNombre !== '') {
            $tipo = $this->resolverLinea($lineaNombre);
        }
        if ($tipo === null) {
            $tipo = $this->detectarTipo($desc);
        }

        // 2. Motos: filtrar por marca si viene
        $motos = $marcaNombre !== null && $marcaNombre !== ''
            ? $this->detectarMotosPorMarca($desc, $marcaNombre)
            : $this->detectarMotos($desc);

        // 3. registrar estado y salir si falta alguno
        if (!$tipo && empty($motos)) {
            $this->db->table('productos')->where('id', $productoId)->update(['enrich_estado' => 'sin_ambos']);
            return;
        }
        if (!$tipo) {
            $this->db->table('productos')->where('id', $productoId)->update(['enrich_estado' => 'sin_tipo']);
            return;
        }
        if (empty($motos)) {
            $this->db->table('productos')->where('id', $productoId)->update(['enrich_estado' => 'sin_moto']);
            return;
        }

        // 3. detectar rango de años desde la descripción original
        [$anioDe, $anioHasta] = $this->detectarAnios($descripcion);

        // 4. crear pieza_maestra
        $piezaId = $this->getOrCreatePiezaMaestra($tipo, $motos[0]);

        // 5. asignar producto + marcar ok
        $this->db->table('productos')
            ->where('id', $productoId)
            ->update(['pieza_maestra_id' => $piezaId, 'enrich_estado' => 'ok']);

        // 6. compatibilidades
        foreach ($motos as $motoId) {
            $this->upsertCompatibilidad($piezaId, $motoId, $anioDe, $anioHasta);
        }
    }

    /**
     * Mapea el valor de la columna "linea" del Excel al nombre canónico del tipo
     * (clave del mapTipos). Primero busca coincidencia exacta, luego por substring.
     */
    private function resolverLinea(string $linea): ?string
    {
        $norm = $this->normalize($linea);

        // Coincidencia exacta (case insensitive) con claves del mapa
        foreach ($this->mapTipos as $tipo => $keywords) {
            if ($this->normalize($tipo) === $norm) {
                return $tipo;
            }
        }

        // Coincidencia por keyword dentro del mapa
        foreach ($this->mapTipos as $tipo => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($norm, $this->normalize($kw))) {
                    return $tipo;
                }
            }
        }

        // Si no coincide con ninguno conocido, devolver la linea tal cual (se creará nueva pieza maestra)
        return $linea;
    }

    /**
     * Detecta motocicletas filtrando alias únicamente de la marca indicada.
     * Más preciso que detectarMotos() cuando se conoce la marca.
     *
     * @return int[]
     */
    private function detectarMotosPorMarca(string $desc, string $marcaNombre): array
    {
        // Buscar marca_id
        $marca = $this->db->table('marcas')
            ->select('id')
            ->where('LOWER(nombre)', mb_strtolower(trim($marcaNombre)))
            ->get()->getRowArray();

        if (!$marca || $marca['id'] === null) {
            // Fallback: búsqueda libre
            return $this->detectarMotos($desc);
        }

        $marcaId = (int) $marca['id'];
        $desc    = $this->normalize($desc);
        $found   = [];

        foreach ($this->getAliases() as $a) {
            // Solo aliases de motos de esta marca
            $motoMarcaId = $this->getMotoMarcaId((int) $a['motocicleta_id']);
            if ($motoMarcaId !== $marcaId) {
                continue;
            }

            $needle = $this->normalize($a['alias']);
            if ($needle === '') {
                continue;
            }

            if (str_contains($desc, $needle)) {
                $motoId = (int) $a['motocicleta_id'];
                if (!in_array($motoId, $found, true)) {
                    $found[] = $motoId;
                }
            }
        }

        return $found;
    }

    /** Cache de motocicleta_id → marca_id para no repetir queries. */
    private array $motoMarcaCache = [];

    private function getMotoMarcaId(int $motoId): ?int
    {
        if (!isset($this->motoMarcaCache[$motoId])) {
            $row = $this->db->table('motocicletas')
                ->select('marca_id')
                ->where('id', $motoId)
                ->get()->getRowArray();
            $this->motoMarcaCache[$motoId] = $row ? (int) $row['marca_id'] : null;
        }
        return $this->motoMarcaCache[$motoId];
    }

    /**
     * Reintenta el enriquecimiento de todos los productos pendientes (enrich_estado != 'ok').
     * Limpia el aliasCache para tomar los aliases recién agregados.
     *
     * @return array{ok: int, pendientes: int}
     */
    public function reenrichPendientes(): array
    {
        // Limpiar cache para que detectarMotos() vea los aliases nuevos
        $this->aliasCache        = [];
        $this->motoMarcaCache    = [];
        $this->piezaMaestraCache = [];
        $this->motoLabelCache    = [];

        $productos = $this->db->table('productos')
            ->select('id, nombre, clave_proveedor, proveedor_id')
            ->whereIn('enrich_estado', ['sin_tipo', 'sin_moto', 'sin_ambos'])
            ->orWhere('enrich_estado IS NULL')
            ->get()->getResultArray();

        $ok         = 0;
        $pendientes = 0;

        foreach ($productos as $p) {
            // Recuperar marca/linea del import_item original si existe
            $item = $this->db->table('import_items')
                ->select('marca, linea')
                ->where('clave_proveedor', $p['clave_proveedor'])
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            $marca = $item['marca'] ?? null;
            $linea = $item['linea'] ?? null;

            $this->enrichProducto((int) $p['id'], $p['nombre'], $marca, $linea);

            $nuevo = $this->db->table('productos')
                ->select('enrich_estado')
                ->where('id', $p['id'])
                ->get()->getRowArray();

            if (($nuevo['enrich_estado'] ?? '') === 'ok') {
                $ok++;
            } else {
                $pendientes++;
            }
        }

        return ['ok' => $ok, 'pendientes' => $pendientes];
    }

    private function normalize(string $text): string
    {
        $text = strtoupper($text);
        $text = str_replace(['-', '_'], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function getAliases(): array
    {
        if (empty($this->aliasCache)) {
            $this->aliasCache = $this->db->table('alias_motos')
                ->select('motocicleta_id, alias')
                ->get()->getResultArray();
        }
        return $this->aliasCache;
    }

    /** @return int[] lista de motocicleta_id detectados en la descripción */
    private function detectarMotos(string $desc): array
    {
        $found = [];
        $desc  = $this->normalize($desc);

        foreach ($this->getAliases() as $a) {

            $needle = $this->normalize($a['alias']);

            if ($needle === '') {
                continue;
            }

            if (str_contains($desc, $needle)) {

                $motoId = (int) $a['motocicleta_id'];

                if (!in_array($motoId, $found, true)) {
                    $found[] = $motoId;
                }
            }
        }

        return $found;
    }

    private function detectarTipo(string $desc): ?string
    {
        $desc = $this->normalize($desc);

        foreach ($this->mapTipos as $tipo => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($desc, $this->normalize($kw))) {
                    return $tipo;
                }
            }
        }

        return null;
    }

    private function getOrCreatePiezaMaestra(string $tipo, int $motoId): int
    {
        // Clave única: tipo + marca-modelo → BAL-TRA|ITALIKA-RT200
        $label  = $this->getMotoLabel($motoId);
        $nombre = $tipo . '|' . $label;

        if (isset($this->piezaMaestraCache[$nombre])) {
            return $this->piezaMaestraCache[$nombre];
        }

        $existing = $this->db->table('piezas_maestras')
            ->where('nombre', $nombre)
            ->get()->getRowArray();

        if ($existing) {
            $this->piezaMaestraCache[$nombre] = (int) $existing['id'];
            return (int) $existing['id'];
        }

        $slug = $this->makeSlug($nombre, 'piezas_maestras');
        $this->db->table('piezas_maestras')->insert([
            'nombre'     => $nombre,
            'slug'       => $slug,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->piezaMaestraCache[$nombre] = $id;
        return $id;
    }

    /** Devuelve 'MARCA-MODELO' para usar en el nombre de pieza_maestra. */
    private function getMotoLabel(int $motoId): string
    {
        if (!isset($this->motoLabelCache[$motoId])) {
            $row = $this->db->table('motocicletas mo')
                ->select('UPPER(ma.nombre) as marca, UPPER(mo.modelo) as modelo')
                ->join('marcas ma', 'ma.id = mo.marca_id')
                ->where('mo.id', $motoId)
                ->get()->getRowArray();
            $this->motoLabelCache[$motoId] = $row
                ? $row['marca'] . '-' . $row['modelo']
                : (string) $motoId;
        }
        return $this->motoLabelCache[$motoId];
    }

    private function upsertCompatibilidad(int $piezaId, int $motoId, ?int $anioDe = null, ?int $anioHasta = null): void
    {
        $exists = $this->db->table('compatibilidades')
            ->where('pieza_maestra_id', $piezaId)
            ->where('motocicleta_id', $motoId)
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('compatibilidades')->insert([
                'pieza_maestra_id'        => $piezaId,
                'motocicleta_id'          => $motoId,
                'anio_desde'              => $anioDe,
                'anio_hasta'              => $anioHasta,
                'confirmada'              => 0,
                'contador_confirmaciones' => 0,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ]);
        } elseif ($anioDe !== null || $anioHasta !== null) {
            // Actualizar años si el registro ya existía sin ellos
            $this->db->table('compatibilidades')
                ->where('pieza_maestra_id', $piezaId)
                ->where('motocicleta_id', $motoId)
                ->where('anio_desde IS NULL')
                ->update([
                    'anio_desde' => $anioDe,
                    'anio_hasta' => $anioHasta,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    /**
     * Extrae rango de años desde patrones como (06/13), (2006/13), (14), (2006/2013).
     * Los años de 2 dígitos se expanden a 20xx (contexto: motos 2000-2030).
     *
     * @return array{0: int|null, 1: int|null}  [anio_desde, anio_hasta]
     */
    private function detectarAnios(string $desc): array
    {
        // Patrón: (DD/DD), (DDDD/DD), (DD/DDDD), (DDDD/DDDD), (DD), (DDDD)
        if (!preg_match('/\((\d{2,4})(?:\/(\d{2,4}))?\)/', $desc, $m)) {
            return [null, null];
        }

        $expand = static function (string $y): int {
            $n = (int) $y;
            return strlen($y) === 2 ? 2000 + $n : $n;
        };

        $desde = $expand($m[1]);
        $hasta = isset($m[2]) ? $expand($m[2]) : $desde;

        // Sanidad: rango razonable para motos
        if ($desde < 1980 || $desde > 2040 || $hasta < $desde) {
            return [null, null];
        }

        return [$desde, $hasta];
    }
}

