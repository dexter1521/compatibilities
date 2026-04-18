<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Crea motocicletas y aliases que NO siguen el patrón estándar (FT150, NS200…):
 *  - Serie Z de Italika: 125Z, 150Z, 170Z, 200Z, 250Z, 250SZ
 *  - Serie D/X motonetas Italika: D125, D150, X125, X150
 *  - Italika FORZA (alias para FT125/FT150 existentes)
 *  - Vento modelos nombre: ROCKETMAN250, ATOM150, NITROX200/250,
 *    TORNADO250, WORKMAN150/250, SCREAMER250, THUNDERSTAR250,
 *    CYCLONE200, XPRESS150, EXPRESS150, CROSSMAX200/250
 *  - Bajaj: DOMINAR250, DOMINAR400  + alias PULSAR200NS→NS200
 *  - Bajaj PULSAR extra aliases
 *
 * Uso: php spark db:seed AliasesFaltantes
 */
class AliasesFaltantes extends Seeder
{
    /**
     * Define lotes de motos a crear (con sus aliases personalizados).
     * Estructura:
     *   ['marca' => 'MARCA', 'modelo' => 'MODELO', 'aliases' => [...]]
     *
     * Si la moto ya existe (por marca+modelo) solo se crean los aliases faltantes.
     */
    private const LOTES = [
        // ── Italika serie Z ─────────────────────────────────────────────────
        ['marca' => 'ITALIKA', 'modelo' => '125Z',  'aliases' => ['125Z', '125 Z', 'ITALIKA 125Z']],
        ['marca' => 'ITALIKA', 'modelo' => '150Z',  'aliases' => ['150Z', '150 Z', 'ITALIKA 150Z', '150SZ']],
        ['marca' => 'ITALIKA', 'modelo' => '170Z',  'aliases' => ['170Z', '170 Z']],
        ['marca' => 'ITALIKA', 'modelo' => '200Z',  'aliases' => ['200Z', '200 Z', 'DM200Z']],
        ['marca' => 'ITALIKA', 'modelo' => '250Z',  'aliases' => ['250Z', '250 Z', 'ITALIKA 250Z']],
        ['marca' => 'ITALIKA', 'modelo' => '250SZ', 'aliases' => ['250SZ', '250 SZ', 'SZ 250']],

        // ── Italika serie D / X motonetas ───────────────────────────────────
        ['marca' => 'ITALIKA', 'modelo' => 'D125',  'aliases' => ['D125', 'D 125', 'D125LT', 'MODENA125', 'VITALIA125']],
        ['marca' => 'ITALIKA', 'modelo' => 'D150',  'aliases' => ['D150', 'D 150', 'D150LT']],
        ['marca' => 'ITALIKA', 'modelo' => 'X125',  'aliases' => ['X125', 'X 125', 'X125G']],
        ['marca' => 'ITALIKA', 'modelo' => 'X150',  'aliases' => ['X150', 'X150D', 'X 150']],

        // ── Italika FORZA (alias de FT existente) ──  creará la moto solo si no existe
        ['marca' => 'ITALIKA', 'modelo' => 'FORZA125', 'aliases' => ['FORZA125', 'FORZA 125', 'FORZA-125']],
        ['marca' => 'ITALIKA', 'modelo' => 'FORZA150', 'aliases' => ['FORZA150', 'FORZA 150', 'FORZA-150']],

        // ── Vento modelos nominales ─────────────────────────────────────────
        ['marca' => 'VENTO', 'modelo' => 'ROCKETMAN250', 'aliases' => ['ROCKETMAN250', 'ROCKETMAN 250', 'ROCKETMAN']],
        ['marca' => 'VENTO', 'modelo' => 'ATOM150',      'aliases' => ['ATOM150', 'ATOM 150']],
        ['marca' => 'VENTO', 'modelo' => 'NITROX200',    'aliases' => ['NITROX200', 'NITROX 200']],
        ['marca' => 'VENTO', 'modelo' => 'NITROX250',    'aliases' => ['NITROX250', 'NITROX 250']],
        ['marca' => 'VENTO', 'modelo' => 'TORNADO250',   'aliases' => ['TORNADO250', 'TORNADO 250', 'TORNADO']],
        ['marca' => 'VENTO', 'modelo' => 'WORKMAN150',   'aliases' => ['WORKMAN150', 'WORKMAN 150']],
        ['marca' => 'VENTO', 'modelo' => 'WORKMAN250',   'aliases' => ['WORKMAN250', 'WORKMAN 250', 'WORKMAN']],
        ['marca' => 'VENTO', 'modelo' => 'SCREAMER250',  'aliases' => ['SCREAMER250', 'SCREAMER 250', 'SCREAMER']],
        ['marca' => 'VENTO', 'modelo' => 'THUNDERSTAR250', 'aliases' => ['THUNDERSTAR250', 'THUNDERSTAR 250', 'THUNDERSTAR']],
        ['marca' => 'VENTO', 'modelo' => 'CYCLONE200',   'aliases' => ['CYCLONE200', 'CYCLONE 200']],
        ['marca' => 'VENTO', 'modelo' => 'XPRESS150',    'aliases' => ['XPRESS150', 'XPRESS 150']],
        ['marca' => 'VENTO', 'modelo' => 'EXPRESS150',   'aliases' => ['EXPRESS150', 'EXPRESS 150']],
        ['marca' => 'VENTO', 'modelo' => 'CROSSMAX200',  'aliases' => ['CROSSMAX200', 'CROSSMAX 200', 'CROSSMAX']],
        ['marca' => 'VENTO', 'modelo' => 'CROSSMAX250',  'aliases' => ['CROSSMAX250', 'CROSSMAX 250']],
        ['marca' => 'VENTO', 'modelo' => 'ALPINA300',    'aliases' => ['ALPINA300', 'ALPINA 300']],
        ['marca' => 'VENTO', 'modelo' => 'RYDER150',     'aliases' => ['RYDER150', 'RYDER 150', 'RYDER']],

        // ── Bajaj extras ────────────────────────────────────────────────────
        ['marca' => 'BAJAJ', 'modelo' => 'DOMINAR250', 'aliases' => ['DOMINAR250', 'DOMINAR 250']],
        ['marca' => 'BAJAJ', 'modelo' => 'DOMINAR400', 'aliases' => ['DOMINAR400', 'DOMINAR 400', 'DOMINAR']],
        // NS200 ya existe → solo agrega aliases extra
        ['marca' => 'BAJAJ', 'modelo' => 'NS200', 'aliases' => ['PULSAR200NS', 'PULSAR 200NS', 'PULSAR 200 NS', 'PULSAR NS 200']],

        // ── KTM extras ──────────────────────────────────────────────────────
        ['marca' => 'KTM', 'modelo' => 'DUKE390', 'aliases' => ['DUKE390', 'DUKE 390', 'KTM390', 'KTM 390']],
        ['marca' => 'KTM', 'modelo' => 'DUKE250', 'aliases' => ['DUKE250', 'DUKE 250', 'KTM250']],
        ['marca' => 'KTM', 'modelo' => 'DUKE200', 'aliases' => ['DUKE200', 'DUKE 200', 'KTM200']],

        // ── Honda extras ────────────────────────────────────────────────────
        ['marca' => 'HONDA', 'modelo' => 'INVICTA150', 'aliases' => ['INVICTA150', 'INVICTA 150', 'INVICTA']],

        // ── Yamaha extras ───────────────────────────────────────────────────
        ['marca' => 'YAMAHA', 'modelo' => 'FZ16', 'aliases' => ['FZ16', 'FZ 16', 'FZ-16']],

        // ── Suzuki extras ───────────────────────────────────────────────────
        ['marca' => 'SUZUKI', 'modelo' => 'GIXXER150', 'aliases' => ['GIXXER150', 'GIXXER 150', 'GIXXER 150/155', 'GIXXER']],

        // ── Honda extras ────────────────────────────────────────────────────
        ['marca' => 'HONDA', 'modelo' => 'CARGO150', 'aliases' => ['CARGO150', 'CARGO 150', 'CARGO']],

        // ── Italika extras ──────────────────────────────────────────────────
        ['marca' => 'ITALIKA', 'modelo' => 'SPITFIRE200', 'aliases' => ['SPITFIRE200', 'SPITFIRE 200', 'SPITFIRE', 'SPTFIRE200', 'SPTFIRE 200', 'SPTFIRE']],

        // ── EK ──────────────────────────────────────────────────────────────
        ['marca' => 'EK', 'modelo' => 'TX200', 'aliases' => ['TX200', 'TX 200']],
    ];

    public function run(): void
    {
        $db = \Config\Database::connect();

        $marcaCache  = [];
        $creadas     = 0;
        $yaExiste    = 0;
        $aliasTotal  = 0;

        foreach (self::LOTES as $lote) {
            $marcaNombre = $lote['marca'];
            $modelo      = $lote['modelo'];
            $aliases     = $lote['aliases'];

            // Obtener/crear marca
            if (!isset($marcaCache[$marcaNombre])) {
                $m = $db->table('marcas')->where('UPPER(nombre)', $marcaNombre)->get()->getRowArray();
                if (!$m) {
                    $db->table('marcas')->insert([
                        'nombre'     => $marcaNombre,
                        'slug'       => strtolower($marcaNombre),
                        'activo'     => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $marcaCache[$marcaNombre] = (int) $db->insertID();
                } else {
                    $marcaCache[$marcaNombre] = (int) $m['id'];
                }
            }
            $marcaId = $marcaCache[$marcaNombre];

            // Obtener/crear moto
            $moto = $db->table('motocicletas')
                ->where('marca_id', $marcaId)
                ->where('UPPER(modelo)', strtoupper($modelo))
                ->get()->getRowArray();

            if ($moto) {
                $motoId = (int) $moto['id'];
                $yaExiste++;
            } else {
                $slugBase = strtolower($marcaNombre . '-' . $modelo);
                $slug = $slugBase;
                $i = 1;
                while ($db->table('motocicletas')->where('slug', $slug)->countAllResults() > 0) {
                    $slug = $slugBase . '-' . $i++;
                }
                $db->table('motocicletas')->insert([
                    'marca_id'   => $marcaId,
                    'modelo'     => strtoupper($modelo),
                    'slug'       => $slug,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $motoId = (int) $db->insertID();
                $creadas++;
            }

            // Insertar aliases (global unique check)
            foreach ($aliases as $alias) {
                $existe = $db->table('alias_motos')
                    ->where('UPPER(alias)', strtoupper($alias))
                    ->countAllResults();
                if ($existe > 0) {
                    continue;
                }
                $slugBase = strtolower(str_replace([' ', '/'], '-', $alias));
                $slug = $slugBase;
                $i = 1;
                while ($db->table('alias_motos')->where('slug', $slug)->countAllResults() > 0) {
                    $slug = $slugBase . '-' . $i++;
                }
                $db->table('alias_motos')->insert([
                    'motocicleta_id' => $motoId,
                    'alias'          => strtoupper($alias),
                    'slug'           => $slug,
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
                $aliasTotal++;
            }
        }

        echo "Motos creadas   : {$creadas}" . PHP_EOL;
        echo "Ya existían     : {$yaExiste}" . PHP_EOL;
        echo "Aliases creados : {$aliasTotal}" . PHP_EOL;
    }
}
