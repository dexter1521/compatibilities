<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Inserta las motocicletas detectadas en modelos_detectados_raw que aún no
 * existen en la tabla motocicletas, junto con sus aliases estándar.
 *
 * Uso: php spark db:seed MotosFaltantes
 */
class MotosFaltantes extends Seeder
{
    /**
     * Modelos detectados → marca canónica.
     * Clave: MODELO (tal como aparece en los nombres de productos, UPPER).
     * Valor: nombre exacto de la marca en la tabla marcas.
     */
    private const MAPA = [
        // ITALIKA — motonetas
        'CS125'   => 'ITALIKA',
        'DSG125'  => 'ITALIKA',
        'GTS175'  => 'ITALIKA',
        'TS170'   => 'ITALIKA',
        'XFT125'  => 'ITALIKA',
        // ITALIKA — motos
        'RT125'   => 'ITALIKA',
        'RT180'   => 'ITALIKA',
        'RT200'   => 'ITALIKA',
        'RT250'   => 'ITALIKA',
        'SZ250'   => 'ITALIKA',
        'RC125'   => 'ITALIKA',
        'RC250'   => 'ITALIKA',
        // BAJAJ
        'NS150'   => 'BAJAJ',
        // VENTO
        'GTS300'  => 'VENTO',
        // YAMAHA
        'YBR125'  => 'YAMAHA',
        // KTM
        'RC390'   => 'KTM',
        // SUZUKI
        'EN150'   => 'SUZUKI',
    ];

    public function run(): void
    {
        $db = $this->db;

        // Cache marca_nombre → marca_id
        $marcaCache = [];

        $creadas  = 0;
        $yaExiste = 0;
        $aliases  = 0;

        foreach (self::MAPA as $modelo => $marcaNombre) {
            // Obtener marca_id (con cache)
            if (!isset($marcaCache[$marcaNombre])) {
                $m = $db->table('marcas')
                    ->where('UPPER(nombre)', $marcaNombre)
                    ->get()->getRowArray();

                if (!$m) {
                    // Crear la marca si no existe (ej. KTM)
                    $slug = strtolower(str_replace(' ', '-', $marcaNombre));
                    $db->table('marcas')->insert([
                        'nombre'     => $marcaNombre,
                        'slug'       => $slug,
                        'activo'     => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $marcaCache[$marcaNombre] = (int) $db->insertID();
                } else {
                    $marcaCache[$marcaNombre] = (int) $m['id'];
                }
            }

            $marcaId = $marcaCache[$marcaNombre];

            // ¿Ya existe la moto?
            $existe = $db->table('motocicletas')
                ->where('marca_id', $marcaId)
                ->where('UPPER(modelo)', $modelo)
                ->get()->getRowArray();

            if ($existe) {
                $motoId = (int) $existe['id'];
                $yaExiste++;
            } else {
                // Generar slug único
                $slugBase = strtolower($marcaNombre . '-' . $modelo);
                $slug     = $slugBase;
                $i        = 1;
                while ($db->table('motocicletas')->where('slug', $slug)->countAllResults() > 0) {
                    $slug = $slugBase . '-' . $i++;
                }

                $db->table('motocicletas')->insert([
                    'marca_id'   => $marcaId,
                    'modelo'     => $modelo,
                    'slug'       => $slug,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $motoId = (int) $db->insertID();
                $creadas++;
            }

            // Generar aliases: FT125, FT 125, FT-125
            $aliases += $this->insertarAliases($db, $motoId, $modelo);
        }

        // Limpiar modelos_detectados_raw de los que ya fueron procesados
        $db->table('modelos_detectados_raw')
            ->whereIn('texto_detectado', array_keys(self::MAPA))
            ->delete();

        echo "Motos creadas   : {$creadas}" . PHP_EOL;
        echo "Ya existían     : {$yaExiste}" . PHP_EOL;
        echo "Aliases creados : {$aliases}" . PHP_EOL;
    }

    private function insertarAliases($db, int $motoId, string $modelo): int
    {
        preg_match('/^([A-Z]+)(\d+[A-Z]?)$/', $modelo, $parts);
        if (!$parts) {
            return 0;
        }
        $prefix = $parts[1]; // FT
        $num    = $parts[2]; // 125

        $variantes = [
            $modelo,                  // FT125
            "{$prefix} {$num}",       // FT 125
            "{$prefix}-{$num}",       // FT-125
        ];

        $creados = 0;
        foreach ($variantes as $alias) {
            $existe = $db->table('alias_motos')
                ->where('UPPER(alias)', strtoupper($alias))
                ->countAllResults();

            if ($existe > 0) {
                continue;
            }

            $slugBase = strtolower(str_replace(' ', '-', $alias));
            $slug     = $slugBase;
            $i        = 1;
            while ($db->table('alias_motos')->where('slug', $slug)->countAllResults() > 0) {
                $slug = $slugBase . '-' . $i++;
            }

            $db->table('alias_motos')->insert([
                'motocicleta_id' => $motoId,
                'alias'          => strtoupper($alias),
                'slug'           => $slug,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $creados++;
        }

        return $creados;
    }
}
