<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseConnection;
use Throwable;

class Home extends BaseController
{
    public function index(): string
    {
        $db   = db_connect();
        $kpis = $this->buildKpis($db);

        return view('dashboard/index', [
            'title'             => 'Compatibilidades | Dashboard',
            'pageTitle'         => 'Panel de Compatibilidades',
            'kpis'              => $kpis,
            'topBusquedas'      => $this->topBusquedas($db),
            'ultimosImports'    => $this->ultimosImports($db),
        ]);
    }

    private function buildKpis(BaseConnection $db): array
    {
        $kpis = [
            'motos'            => 0,
            'piezas'           => 0,
            'compatibilidades' => 0,
            'confirmadas'      => 0,
            'productos'        => 0,
            'busquedas_miss'   => 0,
        ];

        try {
            $kpis['motos']            = (int) $db->table('motocicletas')->countAllResults();
            $kpis['piezas']           = (int) $db->table('piezas_maestras')->countAllResults();
            $kpis['compatibilidades'] = (int) $db->table('compatibilidades')->countAllResults();
            $kpis['confirmadas']      = (int) $db->table('compatibilidades')->where('confirmada', 1)->countAllResults();
            $kpis['productos']        = (int) $db->table('productos')->countAllResults();
            $kpis['busquedas_miss']   = (int) $db->table('busquedas_no_encontradas')->countAllResults();
        } catch (Throwable $exception) {
            log_message('warning', 'KPIs: {error}', ['error' => $exception->getMessage()]);
        }

        return $kpis;
    }

    private function topBusquedas(BaseConnection $db): array
    {
        try {
            return $db->table('busquedas_no_encontradas')
                ->orderBy('contador', 'DESC')
                ->limit(7)
                ->get()
                ->getResultArray();
        } catch (Throwable) {
            return [];
        }
    }

    private function ultimosImports(BaseConnection $db): array
    {
        try {
            return $db->table('import_jobs')
                ->orderBy('id', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        } catch (Throwable) {
            return [];
        }
    }
}
