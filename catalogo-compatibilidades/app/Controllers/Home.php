<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseConnection;
use Throwable;

class Home extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index', [
            'title'         => 'Compatibilidades | Dashboard',
            'pageTitle'     => 'Panel de Compatibilidades',
            'kpis'          => $this->buildKpis(),
        ]);
    }

    /**
     * Lee conteos basicos del MVP sin romper si falta una tabla en desarrollo.
     *
     * @return array<string,int>
     */
    private function buildKpis(): array
    {
        $kpis = [
            'motos'            => 0,
            'piezas'           => 0,
            'compatibilidades' => 0,
        ];

        try {
            /** @var BaseConnection $db */
            $db = db_connect();
            $kpis['motos'] = (int) $db->table('motocicletas')->countAllResults();
            $kpis['piezas'] = (int) $db->table('piezas_maestras')->countAllResults();
            $kpis['compatibilidades'] = (int) $db->table('compatibilidades')->countAllResults();
        } catch (Throwable $exception) {
            log_message('warning', 'No se pudieron cargar KPIs iniciales: {error}', ['error' => $exception->getMessage()]);
        }

        return $kpis;
    }
}
