<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Escanea las descripciones (nombre) de los productos para detectar códigos de
 * modelo (p. ej. FT150, DS125, NS200). Por cada modelo encontrado:
 *
 *  - Si ya existe en motocicletas → genera aliases automáticamente.
 *  - Si NO existe → lo guarda en modelos_detectados_raw para revisión manual.
 *
 * Uso:
 *   php spark detectar:modelos
 *   php spark detectar:modelos --solo-nuevos    (no re-genera aliases existentes)
 */
class DetectarModelos extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'detectar:modelos';
    protected $description = 'Detecta modelos de moto desde nombres de productos y genera aliases automáticamente.';

    /** Prefijos válidos de modelos de moto (lower) */
    private const PREFIJOS_VALIDOS = [
        'ft', 'dm', 'dt', 'ds', 'at', 'ws', 'rc', 'ns', 'rs', 'gn', 'gs',
        'cg', 'fz', 'bws', 'terra', 'storm', 'boxer', 'pulsar', 'en', 'ybr',
        'cs', 'dsg', 'gts', 'xft', 'dsr', 'rt', 'sz', 'gl',
    ];

    /** Cache para no re-insertar el mismo alias dos veces en la misma corrida */
    private array $aliasInserted = [];

    public function run(array $params): void
    {
        $db = \Config\Database::connect();

        $soloNuevos = in_array('--solo-nuevos', $params, true);

        $productos = $db->table('productos')
            ->select('id, nombre')
            ->get()
            ->getResultArray();

        $totalProductos = count($productos);
        CLI::write("Procesando {$totalProductos} productos...", 'cyan');

        $aliasCreados  = 0;
        $modelosNuevos = 0;
        $rawInseridos  = [];   // texto_detectado ya guardado en esta corrida

        foreach ($productos as $p) {
            $nombre = $p['nombre'];
            $desc   = $this->normalize($nombre);

            // Detectar códigos tipo FT150, NS200, YBR125, CS125…
            preg_match_all('/\b([a-z]{1,4})\s?-?\s?(\d{2,3})\b/i', $desc, $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $prefijo = strtolower(trim($m[1]));
                $numero  = trim($m[2]);
                $modelo  = strtoupper($prefijo . $numero);  // FT150

                if (!$this->esPrefijoCanonico($prefijo)) {
                    continue;
                }

                // ¿Existe en motocicletas? Búsqueda exacta primero, luego LIKE
                $moto = $db->table('motocicletas')
                    ->select('id, modelo')
                    ->where('UPPER(modelo)', $modelo)
                    ->get()->getRowArray();

                if (!$moto) {
                    $moto = $db->table('motocicletas')
                        ->select('id, modelo')
                        ->like('modelo', $modelo)
                        ->limit(1)
                        ->get()->getRowArray();
                }

                if (!$moto) {
                    // Nuevo: guardar en backlog (una vez por modelo por corrida)
                    if (!in_array($modelo, $rawInseridos, true)) {
                        $db->table('modelos_detectados_raw')->insert([
                            'texto_detectado' => $modelo,
                            'nombre_producto' => $nombre,
                        ]);
                        $rawInseridos[] = $modelo;
                        $modelosNuevos++;
                        CLI::write("  [NUEVO] {$modelo}  ← {$nombre}", 'yellow');
                    }
                    continue;
                }

                // Existe: generar aliases si corresponde
                if (!$soloNuevos) {
                    $creados = $this->crearAliases($db, (int) $moto['id'], $modelo);
                    $aliasCreados += $creados;
                }
            }
        }

        CLI::write("─────────────────────────────────────────", 'dark_gray');
        CLI::write("Aliases creados     : {$aliasCreados}", 'green');
        CLI::write("Modelos sin registrar: {$modelosNuevos} (ver tabla modelos_detectados_raw)", 'yellow');
        CLI::write('✔ Proceso terminado', 'green');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = str_replace(['-', '_', '.', ';'], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function esPrefijoCanonico(string $prefijo): bool
    {
        return in_array(strtolower($prefijo), self::PREFIJOS_VALIDOS, true);
    }

    /**
     * Genera hasta 3 variantes del alias y las inserta si no existen.
     * Devuelve el número de aliases efectivamente creados.
     */
    private function crearAliases(BaseConnection $db, int $motoId, string $modelo): int
    {
        // Separar prefijo y número  (FT + 150)
        preg_match('/^([A-Z]+)(\d+)$/', $modelo, $parts);
        if (!$parts) {
            return 0;
        }
        $prefix = $parts[1]; // FT
        $num    = $parts[2]; // 150

        $variantes = [
            $modelo,                    // FT150
            "{$prefix} {$num}",         // FT 150
            "{$prefix}-{$num}",         // FT-150
        ];

        $creados = 0;

        foreach ($variantes as $alias) {
            $cacheKey = "{$motoId}:{$alias}";
            if (isset($this->aliasInserted[$cacheKey])) {
                continue;
            }
            $this->aliasInserted[$cacheKey] = true;

            $existe = $db->table('alias_motos')
                ->where('UPPER(alias)', strtoupper($alias))
                ->countAllResults();

            if ($existe > 0) {
                continue;
            }

            $slug = strtolower(str_replace(' ', '-', trim($alias)));

            // El slug debe ser único en la tabla
            $slugBase  = $slug;
            $i         = 1;
            while ($db->table('alias_motos')->where('slug', $slug)->countAllResults() > 0) {
                $slug = $slugBase . '-' . $i++;
            }

            $db->table('alias_motos')->insert([
                'motocicleta_id' => $motoId,
                'alias'          => strtoupper($alias),
                'slug'           => $slug,
            ]);
            $creados++;
        }

        return $creados;
    }
}
