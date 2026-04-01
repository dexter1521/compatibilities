<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Datos de demostración realistas para el MVP.
 * Simula un catálogo típico de mostrador de motos en México.
 */
class DemoSeeder extends Seeder
{
    public function run()
    {
        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        // ── Marcas ────────────────────────────────────────────────────────────
        $marcas = [
            ['nombre' => 'Honda',   'slug' => 'honda'],
            ['nombre' => 'Yamaha',  'slug' => 'yamaha'],
            ['nombre' => 'Italika', 'slug' => 'italika'],
            ['nombre' => 'TVS',     'slug' => 'tvs'],
            ['nombre' => 'Vento',   'slug' => 'vento'],
            ['nombre' => 'Suzuki',  'slug' => 'suzuki'],
        ];
        foreach ($marcas as &$m) {
            $m['created_at'] = $now;
            $m['updated_at'] = $now;
        }
        $db->table('marcas')->ignore(true)->insertBatch($marcas);

        $marcaIds = [];
        foreach ($db->table('marcas')->get()->getResultArray() as $row) {
            $marcaIds[$row['slug']] = (int) $row['id'];
        }

        // ── Motocicletas ──────────────────────────────────────────────────────
        $motos = [
            // Honda
            ['marca_id' => $marcaIds['honda'],   'modelo' => 'FT150',         'anio_desde' => 2015, 'anio_hasta' => 2024, 'cilindrada' => '150cc', 'slug' => 'honda-ft150-2015-2024'],
            ['marca_id' => $marcaIds['honda'],   'modelo' => 'CGL125',        'anio_desde' => 2014, 'anio_hasta' => 2023, 'cilindrada' => '125cc', 'slug' => 'honda-cgl125-2014-2023'],
            ['marca_id' => $marcaIds['honda'],   'modelo' => 'Storm 125',     'anio_desde' => 2018, 'anio_hasta' => 2024, 'cilindrada' => '125cc', 'slug' => 'honda-storm125-2018-2024'],
            ['marca_id' => $marcaIds['honda'],   'modelo' => 'CB190R',        'anio_desde' => 2019, 'anio_hasta' => 2024, 'cilindrada' => '190cc', 'slug' => 'honda-cb190r-2019-2024'],
            // Yamaha
            ['marca_id' => $marcaIds['yamaha'],  'modelo' => 'FZ150',         'anio_desde' => 2016, 'anio_hasta' => 2024, 'cilindrada' => '150cc', 'slug' => 'yamaha-fz150-2016-2024'],
            ['marca_id' => $marcaIds['yamaha'],  'modelo' => 'FZ25',          'anio_desde' => 2018, 'anio_hasta' => 2024, 'cilindrada' => '250cc', 'slug' => 'yamaha-fz25-2018-2024'],
            ['marca_id' => $marcaIds['yamaha'],  'modelo' => 'SZ-RR 150',     'anio_desde' => 2015, 'anio_hasta' => 2022, 'cilindrada' => '150cc', 'slug' => 'yamaha-szrr150-2015-2022'],
            // Italika
            ['marca_id' => $marcaIds['italika'], 'modelo' => 'SR150',         'anio_desde' => 2016, 'anio_hasta' => 2024, 'cilindrada' => '150cc', 'slug' => 'italika-sr150-2016-2024'],
            ['marca_id' => $marcaIds['italika'], 'modelo' => 'GS150',         'anio_desde' => 2017, 'anio_hasta' => 2023, 'cilindrada' => '150cc', 'slug' => 'italika-gs150-2017-2023'],
            ['marca_id' => $marcaIds['italika'], 'modelo' => 'DM200',         'anio_desde' => 2018, 'anio_hasta' => 2024, 'cilindrada' => '200cc', 'slug' => 'italika-dm200-2018-2024'],
            // TVS
            ['marca_id' => $marcaIds['tvs'],     'modelo' => 'Apache RTR 160', 'anio_desde' => 2019, 'anio_hasta' => 2024, 'cilindrada' => '160cc', 'slug' => 'tvs-apache-rtr160-2019-2024'],
            ['marca_id' => $marcaIds['tvs'],     'modelo' => 'King 125',      'anio_desde' => 2017, 'anio_hasta' => 2023, 'cilindrada' => '125cc', 'slug' => 'tvs-king125-2017-2023'],
            // Vento
            ['marca_id' => $marcaIds['vento'],   'modelo' => 'Phantom R5',    'anio_desde' => 2016, 'anio_hasta' => 2022, 'cilindrada' => '150cc', 'slug' => 'vento-phantom-r5-2016-2022'],
            // Suzuki
            ['marca_id' => $marcaIds['suzuki'],  'modelo' => 'GS150R',        'anio_desde' => 2015, 'anio_hasta' => 2023, 'cilindrada' => '150cc', 'slug' => 'suzuki-gs150r-2015-2023'],
        ];
        foreach ($motos as &$m) {
            $m['created_at'] = $now;
            $m['updated_at'] = $now;
        }
        $db->table('motocicletas')->ignore(true)->insertBatch($motos);

        $motoIds = [];
        foreach ($db->table('motocicletas')->get()->getResultArray() as $row) {
            $motoIds[$row['slug']] = (int) $row['id'];
        }

        // ── Alias de motos ────────────────────────────────────────────────────
        $alias = [
            ['motocicleta_id' => $motoIds['honda-ft150-2015-2024'],      'alias' => 'ft150',          'slug' => 'ft150'],
            ['motocicleta_id' => $motoIds['honda-ft150-2015-2024'],      'alias' => 'ft 150',         'slug' => 'ft-150'],
            ['motocicleta_id' => $motoIds['honda-ft150-2015-2024'],      'alias' => 'fan 150',        'slug' => 'fan-150'],
            ['motocicleta_id' => $motoIds['honda-cgl125-2014-2023'],     'alias' => 'cgl125',         'slug' => 'cgl125'],
            ['motocicleta_id' => $motoIds['honda-cgl125-2014-2023'],     'alias' => 'cgl 125',        'slug' => 'cgl-125'],
            ['motocicleta_id' => $motoIds['honda-storm125-2018-2024'],   'alias' => 'storm 125',      'slug' => 'storm-125'],
            ['motocicleta_id' => $motoIds['honda-cb190r-2019-2024'],     'alias' => 'cb190',          'slug' => 'cb190'],
            ['motocicleta_id' => $motoIds['yamaha-fz150-2016-2024'],     'alias' => 'fz150',          'slug' => 'fz150'],
            ['motocicleta_id' => $motoIds['yamaha-fz150-2016-2024'],     'alias' => 'fz 150',         'slug' => 'fz-150'],
            ['motocicleta_id' => $motoIds['yamaha-fz25-2018-2024'],      'alias' => 'fz25',           'slug' => 'fz25'],
            ['motocicleta_id' => $motoIds['yamaha-szrr150-2015-2022'],   'alias' => 'sz-rr',          'slug' => 'sz-rr'],
            ['motocicleta_id' => $motoIds['italika-sr150-2016-2024'],    'alias' => 'sr150',          'slug' => 'sr150-italika'],
            ['motocicleta_id' => $motoIds['italika-gs150-2017-2023'],    'alias' => 'gs150',          'slug' => 'gs150-italika'],
            ['motocicleta_id' => $motoIds['tvs-apache-rtr160-2019-2024'],'alias' => 'apache 160',     'slug' => 'apache-160'],
            ['motocicleta_id' => $motoIds['tvs-apache-rtr160-2019-2024'],'alias' => 'apache rtr',     'slug' => 'apache-rtr'],
            ['motocicleta_id' => $motoIds['suzuki-gs150r-2015-2023'],    'alias' => 'gs150r',         'slug' => 'gs150r-suzuki'],
        ];
        foreach ($alias as &$a) {
            $a['created_at'] = $now;
            $a['updated_at'] = $now;
        }
        $db->table('alias_motos')->ignore(true)->insertBatch($alias);

        // ── Piezas maestras ───────────────────────────────────────────────────
        $piezas = [
            ['nombre' => 'Balata delantera',         'slug' => 'balata-delantera'],
            ['nombre' => 'Balata trasera',            'slug' => 'balata-trasera'],
            ['nombre' => 'Filtro de aceite',          'slug' => 'filtro-aceite'],
            ['nombre' => 'Filtro de aire',            'slug' => 'filtro-aire'],
            ['nombre' => 'Bujia',                     'slug' => 'bujia'],
            ['nombre' => 'Cadena de transmision',     'slug' => 'cadena-transmision'],
            ['nombre' => 'Kit de arrastre',           'slug' => 'kit-arrastre'],
            ['nombre' => 'Disco de freno delantero',  'slug' => 'disco-freno-delantero'],
            ['nombre' => 'Empaque de culata',         'slug' => 'empaque-culata'],
            ['nombre' => 'Clutch completo',           'slug' => 'clutch-completo'],
            ['nombre' => 'Piston y segmentos',        'slug' => 'piston-segmentos'],
            ['nombre' => 'Amortiguador trasero',      'slug' => 'amortiguador-trasero'],
        ];
        foreach ($piezas as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        $db->table('piezas_maestras')->ignore(true)->insertBatch($piezas);

        $piezaIds = [];
        foreach ($db->table('piezas_maestras')->get()->getResultArray() as $row) {
            $piezaIds[$row['slug']] = (int) $row['id'];
        }

        // ── Proveedores ───────────────────────────────────────────────────────
        $proveedores = [
            ['nombre' => 'REMSA',  'slug' => 'remsa'],
            ['nombre' => 'FERODO', 'slug' => 'ferodo'],
            ['nombre' => 'NGK',    'slug' => 'ngk'],
            ['nombre' => 'RK',     'slug' => 'rk'],
            ['nombre' => 'JT',     'slug' => 'jt'],
            ['nombre' => 'HIFLO',  'slug' => 'hiflo'],
            ['nombre' => 'UNITEK', 'slug' => 'unitek'],
        ];
        foreach ($proveedores as &$pv) {
            $pv['created_at'] = $now;
            $pv['updated_at'] = $now;
        }
        $db->table('proveedores')->ignore(true)->insertBatch($proveedores);

        $provIds = [];
        foreach ($db->table('proveedores')->get()->getResultArray() as $row) {
            $provIds[$row['slug']] = (int) $row['id'];
        }

        // ── Productos ─────────────────────────────────────────────────────────
        // [proveedor_slug, pieza_slug, clave, nombre]
        $productosData = [
            // Balatas delanteras
            ['remsa',  'balata-delantera',        'BD-HFT150',  'Balata delantera Honda FT150'],
            ['remsa',  'balata-delantera',        'BD-HCGL125', 'Balata delantera Honda CGL125'],
            ['remsa',  'balata-delantera',        'BD-YFZ150',  'Balata delantera Yamaha FZ150'],
            ['ferodo', 'balata-delantera',        'FDB2215',    'Balata disc. delantera universal 150cc'],
            ['remsa',  'balata-delantera',        'BD-ISR150',  'Balata delantera Italika SR150'],
            ['remsa',  'balata-delantera',        'BD-TAP160',  'Balata delantera TVS Apache 160'],
            // Balatas traseras
            ['remsa',  'balata-trasera',          'BT-HFT150',  'Balata trasera Honda FT150 / CGL125'],
            ['remsa',  'balata-trasera',          'BT-YFZ150',  'Balata trasera Yamaha FZ150'],
            ['ferodo', 'balata-trasera',          'FDB2216',    'Balata disc. trasera universal 150cc'],
            // Filtros de aceite
            ['hiflo',  'filtro-aceite',           'HF138',      'Filtro aceite HF138 Honda 125-160cc'],
            ['hiflo',  'filtro-aceite',           'HF204',      'Filtro aceite HF204 Honda CB / Storm'],
            ['unitek', 'filtro-aceite',           'FO-IT150',   'Filtro aceite Italika 150cc'],
            ['unitek', 'filtro-aceite',           'FO-TVS160',  'Filtro aceite TVS Apache 160'],
            // Filtros de aire
            ['unitek', 'filtro-aire',             'FA-HFT150',  'Filtro aire Honda FT150'],
            ['unitek', 'filtro-aire',             'FA-YFZ150',  'Filtro aire Yamaha FZ150'],
            ['unitek', 'filtro-aire',             'FA-ISR150',  'Filtro aire Italika SR150'],
            // Bujias
            ['ngk',    'bujia',                   'DR8EA',      'Bujia NGK DR8EA - Honda / Yamaha 150cc'],
            ['ngk',    'bujia',                   'CR8E',       'Bujia NGK CR8E - universal'],
            ['ngk',    'bujia',                   'CPR8EA9',    'Bujia NGK CPR8EA-9 iridio 150cc'],
            // Cadenas
            ['rk',     'cadena-transmision',      'RK428MXZ110','Cadena RK 428 x 110 eslabones'],
            ['rk',     'cadena-transmision',      'RK428SB110', 'Cadena RK 428 SB 110 eslabones'],
            // Kit de arrastre
            ['jt',     'kit-arrastre',            'JTK-HFT150', 'Kit arrastre JT Honda FT150 (14/37)'],
            ['jt',     'kit-arrastre',            'JTK-YFZ150', 'Kit arrastre JT Yamaha FZ150 (14/42)'],
            ['jt',     'kit-arrastre',            'JTK-ISR150', 'Kit arrastre JT Italika SR150 (15/38)'],
            // Discos de freno
            ['remsa',  'disco-freno-delantero',   'DF-HFT150',  'Disco freno del. Honda FT150'],
            ['remsa',  'disco-freno-delantero',   'DF-YFZ150',  'Disco freno del. Yamaha FZ150'],
            ['remsa',  'disco-freno-delantero',   'DF-TAP160',  'Disco freno del. TVS Apache 160'],
            // Empaques de culata
            ['unitek', 'empaque-culata',          'EC-HFT150',  'Empaque de culata Honda FT150'],
            ['unitek', 'empaque-culata',          'EC-YFZ150',  'Empaque de culata Yamaha FZ150'],
            // Pistones
            ['unitek', 'piston-segmentos',        'PS-HFT150-STD', 'Piston STD Honda FT150 (57.3mm)'],
            ['unitek', 'piston-segmentos',        'PS-HFT150-025', 'Piston +0.25 Honda FT150 (57.55mm)'],
            ['unitek', 'piston-segmentos',        'PS-YFZ150-STD', 'Piston STD Yamaha FZ150 (57.0mm)'],
            // Amortiguadores
            ['unitek', 'amortiguador-trasero',    'AT-HFT150',  'Amortiguador trasero Honda FT150'],
            ['unitek', 'amortiguador-trasero',    'AT-HCGL125', 'Amortiguador trasero Honda CGL125'],
        ];

        $productos = [];
        foreach ($productosData as $pd) {
            [$pvSlug, $piezaSlug, $clave, $nombre] = $pd;
            $productos[] = [
                'proveedor_id'     => $provIds[$pvSlug],
                'pieza_maestra_id' => $piezaIds[$piezaSlug],
                'clave_proveedor'  => $clave,
                'nombre'           => $nombre,
                'slug'             => mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $clave)),
                'activo'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        $db->table('productos')->ignore(true)->insertBatch($productos);

        // ── Compatibilidades ──────────────────────────────────────────────────
        // [pieza_slug, moto_slug, confirmada, confirmaciones]
        $compats = [
            // Honda FT150
            ['balata-delantera',       'honda-ft150-2015-2024', 1, 12],
            ['balata-trasera',         'honda-ft150-2015-2024', 1, 9],
            ['filtro-aceite',          'honda-ft150-2015-2024', 1, 15],
            ['filtro-aire',            'honda-ft150-2015-2024', 1, 7],
            ['bujia',                  'honda-ft150-2015-2024', 1, 18],
            ['cadena-transmision',     'honda-ft150-2015-2024', 1, 5],
            ['kit-arrastre',           'honda-ft150-2015-2024', 1, 6],
            ['disco-freno-delantero',  'honda-ft150-2015-2024', 1, 3],
            ['empaque-culata',         'honda-ft150-2015-2024', 0, 1],
            ['piston-segmentos',       'honda-ft150-2015-2024', 0, 0],
            ['amortiguador-trasero',   'honda-ft150-2015-2024', 1, 4],
            // Honda CGL125
            ['balata-delantera',       'honda-cgl125-2014-2023', 1, 8],
            ['balata-trasera',         'honda-cgl125-2014-2023', 1, 6],
            ['filtro-aceite',          'honda-cgl125-2014-2023', 1, 10],
            ['bujia',                  'honda-cgl125-2014-2023', 1, 14],
            ['amortiguador-trasero',   'honda-cgl125-2014-2023', 1, 2],
            // Yamaha FZ150
            ['balata-delantera',       'yamaha-fz150-2016-2024', 1, 11],
            ['balata-trasera',         'yamaha-fz150-2016-2024', 1, 8],
            ['filtro-aceite',          'yamaha-fz150-2016-2024', 1, 13],
            ['filtro-aire',            'yamaha-fz150-2016-2024', 1, 5],
            ['bujia',                  'yamaha-fz150-2016-2024', 1, 16],
            ['cadena-transmision',     'yamaha-fz150-2016-2024', 1, 4],
            ['kit-arrastre',           'yamaha-fz150-2016-2024', 1, 3],
            ['disco-freno-delantero',  'yamaha-fz150-2016-2024', 1, 5],
            ['empaque-culata',         'yamaha-fz150-2016-2024', 0, 0],
            ['piston-segmentos',       'yamaha-fz150-2016-2024', 0, 0],
            // Italika SR150
            ['balata-delantera',       'italika-sr150-2016-2024', 1, 7],
            ['balata-trasera',         'italika-sr150-2016-2024', 1, 5],
            ['filtro-aceite',          'italika-sr150-2016-2024', 1, 9],
            ['filtro-aire',            'italika-sr150-2016-2024', 1, 4],
            ['bujia',                  'italika-sr150-2016-2024', 1, 11],
            ['kit-arrastre',           'italika-sr150-2016-2024', 1, 2],
            // TVS Apache 160
            ['balata-delantera',       'tvs-apache-rtr160-2019-2024', 1, 6],
            ['balata-trasera',         'tvs-apache-rtr160-2019-2024', 1, 4],
            ['filtro-aceite',          'tvs-apache-rtr160-2019-2024', 1, 7],
            ['bujia',                  'tvs-apache-rtr160-2019-2024', 1, 9],
            ['disco-freno-delantero',  'tvs-apache-rtr160-2019-2024', 1, 3],
            // Yamaha FZ25
            ['filtro-aceite',          'yamaha-fz25-2018-2024', 1, 5],
            ['bujia',                  'yamaha-fz25-2018-2024', 1, 8],
            ['filtro-aire',            'yamaha-fz25-2018-2024', 0, 1],
            // Suzuki GS150R
            ['balata-delantera',       'suzuki-gs150r-2015-2023', 1, 4],
            ['filtro-aceite',          'suzuki-gs150r-2015-2023', 1, 6],
            ['bujia',                  'suzuki-gs150r-2015-2023', 1, 7],
        ];

        $compatRows = [];
        foreach ($compats as [$piezaSlug, $motoSlug, $confirmada, $confirmaciones]) {
            if (! isset($piezaIds[$piezaSlug], $motoIds[$motoSlug])) {
                continue;
            }
            $compatRows[] = [
                'pieza_maestra_id'        => $piezaIds[$piezaSlug],
                'motocicleta_id'          => $motoIds[$motoSlug],
                'confirmada'              => $confirmada,
                'contador_confirmaciones' => $confirmaciones,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }
        $db->table('compatibilidades')->ignore(true)->insertBatch($compatRows);

        // ── Búsquedas no encontradas (muestra historial) ───────────────────────
        $busquedas = [
            ['termino' => 'carburador ft150',      'termino_normalizado' => 'carburador ft150',      'contador' => 8,  'ultima_busqueda_at' => $now],
            ['termino' => 'bomba de aceite cgl',   'termino_normalizado' => 'bomba de aceite cgl',   'contador' => 5,  'ultima_busqueda_at' => $now],
            ['termino' => 'regulador de voltaje',  'termino_normalizado' => 'regulador de voltaje',  'contador' => 12, 'ultima_busqueda_at' => $now],
            ['termino' => 'bobina encendido fz',   'termino_normalizado' => 'bobina encendido fz',   'contador' => 3,  'ultima_busqueda_at' => $now],
            ['termino' => 'tensor de cadena sr150','termino_normalizado' => 'tensor de cadena sr150','contador' => 6,  'ultima_busqueda_at' => $now],
            ['termino' => 'valvula admision 150',  'termino_normalizado' => 'valvula admision 150',  'contador' => 2,  'ultima_busqueda_at' => $now],
            ['termino' => 'clutch apache 160',     'termino_normalizado' => 'clutch apache 160',     'contador' => 9,  'ultima_busqueda_at' => $now],
        ];
        foreach ($busquedas as &$b) {
            $b['created_at'] = $now;
            $b['updated_at'] = $now;
        }
        $db->table('busquedas_no_encontradas')->ignore(true)->insertBatch($busquedas);

        echo "✓ DemoSeeder completado:\n";
        echo "  - " . count($marcas)      . " marcas\n";
        echo "  - " . count($motos)       . " motocicletas\n";
        echo "  - " . count($alias)       . " alias de motos\n";
        echo "  - " . count($piezas)      . " piezas maestras\n";
        echo "  - " . count($proveedores) . " proveedores\n";
        echo "  - " . count($productos)   . " productos\n";
        echo "  - " . count($compatRows)  . " compatibilidades\n";
        echo "  - " . count($busquedas)   . " búsquedas no encontradas\n";
    }
}
