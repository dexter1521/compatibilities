<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Busca piezas que coincidan con el término dado.
     * Busca en: piezas_maestras.nombre, productos.clave_proveedor,
     * productos.nombre, y alias_motos.alias (vía compatibilidades).
     *
     * @return array<int, array{
     *   pieza_maestra_id: int,
     *   pieza_nombre: string,
     *   productos: list<array{id:int,clave_proveedor:string,nombre:string,proveedor:string}>,
     *   compatibilidades: list<array{id:int,confirmada:int,contador_confirmaciones:int,marca_nombre:string,moto_modelo:string,anio_desde:int|null,anio_hasta:int|null,cilindrada:string|null}>
     * }>
     */
    public function searchByTerm(string $q): array
    {
        $q = trim($q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $safe = '%' . $q . '%';

        // Step 1: resolve distinct pieza_maestra IDs que coincidan con el término
        $sql1 = "
            SELECT DISTINCT pm.id AS pieza_maestra_id
            FROM piezas_maestras pm
            LEFT JOIN productos p ON p.pieza_maestra_id = pm.id AND p.activo = 1
            LEFT JOIN compatibilidades c ON c.pieza_maestra_id = pm.id
            LEFT JOIN alias_motos am ON am.motocicleta_id = c.motocicleta_id
            WHERE pm.nombre LIKE ?
               OR p.clave_proveedor LIKE ?
               OR p.nombre LIKE ?
               OR am.alias LIKE ?
            LIMIT 50
        ";

        $piezaRows = $this->db->query($sql1, [$safe, $safe, $safe, $safe])->getResultArray();
        $piezaIds  = array_column($piezaRows, 'pieza_maestra_id');

        if (empty($piezaIds)) {
            return [];
        }

        // Step 2: obtener detalle completo para esas piezas
        $placeholders = implode(',', array_fill(0, count($piezaIds), '?'));

        $sql2 = "
            SELECT
                pm.id            AS pieza_maestra_id,
                pm.nombre        AS pieza_nombre,
                p.id             AS producto_id,
                p.clave_proveedor,
                p.nombre         AS producto_nombre,
                pr.nombre        AS proveedor_nombre,
                c.id             AS compat_id,
                c.confirmada,
                c.contador_confirmaciones,
                mo.id            AS moto_id,
                ma.nombre        AS marca_nombre,
                mo.modelo        AS moto_modelo,
                mo.anio_desde,
                mo.anio_hasta,
                mo.cilindrada
            FROM piezas_maestras pm
            LEFT JOIN productos p        ON p.pieza_maestra_id = pm.id AND p.activo = 1
            LEFT JOIN proveedores pr     ON pr.id = p.proveedor_id
            LEFT JOIN compatibilidades c ON c.pieza_maestra_id = pm.id
            LEFT JOIN motocicletas mo    ON mo.id = c.motocicleta_id
            LEFT JOIN marcas ma          ON ma.id = mo.marca_id
            WHERE pm.id IN ($placeholders)
            ORDER BY pm.nombre, ma.nombre, mo.modelo
        ";

        $rows = $this->db->query($sql2, $piezaIds)->getResultArray();

        // Step 3: agrupar por pieza_maestra_id
        $results = [];

        foreach ($rows as $row) {
            $pid = (int) $row['pieza_maestra_id'];

            if (!isset($results[$pid])) {
                $results[$pid] = [
                    'pieza_maestra_id' => $pid,
                    'pieza_nombre'     => $row['pieza_nombre'],
                    'productos'        => [],
                    'compatibilidades' => [],
                ];
            }

            // Producto único por ID
            if ($row['producto_id'] !== null) {
                $prodId = (int) $row['producto_id'];
                if (!isset($results[$pid]['productos'][$prodId])) {
                    $results[$pid]['productos'][$prodId] = [
                        'id'              => $prodId,
                        'clave_proveedor' => $row['clave_proveedor'],
                        'nombre'          => $row['producto_nombre'],
                        'proveedor'       => $row['proveedor_nombre'],
                    ];
                }
            }

            // Compatibilidad única por ID
            if ($row['compat_id'] !== null) {
                $cid = (int) $row['compat_id'];
                if (!isset($results[$pid]['compatibilidades'][$cid])) {
                    $results[$pid]['compatibilidades'][$cid] = [
                        'id'                      => $cid,
                        'confirmada'              => (int) $row['confirmada'],
                        'contador_confirmaciones' => (int) $row['contador_confirmaciones'],
                        'marca_nombre'            => $row['marca_nombre'],
                        'moto_modelo'             => $row['moto_modelo'],
                        'anio_desde'              => $row['anio_desde'],
                        'anio_hasta'              => $row['anio_hasta'],
                        'cilindrada'              => $row['cilindrada'],
                    ];
                }
            }
        }

        // Normalizar a arrays indexados
        foreach ($results as &$r) {
            $r['productos']        = array_values($r['productos']);
            $r['compatibilidades'] = array_values($r['compatibilidades']);
        }
        unset($r);

        return array_values($results);
    }

    /**
     * Registra o incrementa un término no encontrado.
     * Usa UPSERT para evitar duplicados por termino_normalizado.
     */
    public function logMissedSearch(string $term): void
    {
        $normalizado = mb_strtolower(trim($term));

        $existing = $this->db->table('busquedas_no_encontradas')
            ->where('termino_normalizado', $normalizado)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('busquedas_no_encontradas')
                ->where('id', $existing['id'])
                ->update([
                    'contador'           => (int) $existing['contador'] + 1,
                    'ultima_busqueda_at' => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
        } else {
            $this->db->table('busquedas_no_encontradas')
                ->insert([
                    'termino'             => $term,
                    'termino_normalizado' => $normalizado,
                    'contador'            => 1,
                    'ultima_busqueda_at'  => date('Y-m-d H:i:s'),
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
        }
    }
}
