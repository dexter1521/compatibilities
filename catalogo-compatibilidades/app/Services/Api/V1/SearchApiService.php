<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\SearchModel;
use CodeIgniter\Database\BaseConnection;

class SearchApiService
{
    private SearchModel $searchModel;
    private BaseConnection $db;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
        $this->db = \Config\Database::connect();
    }

    public function search(string $term): array
    {
        return $this->searchModel->searchByTerm($term);
    }

    public function confirmCompatibilidad(int $id): ?array
    {
        $this->db->transStart();

        $row = $this->db->table('compatibilidades')->where('id', $id)->get()->getRowArray();

        if (!$row) {
            $this->db->transComplete();
            return null;
        }

        $nuevoContador = (int) $row['contador_confirmaciones'] + 1;

        $this->db->table('compatibilidades')
            ->where('id', $id)
            ->update([
                'confirmada'              => 1,
                'contador_confirmaciones' => $nuevoContador,
                'updated_at'              => date('Y-m-d H:i:s'),
            ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('No fue posible confirmar la compatibilidad.');
        }

        return [
            'id'                      => $id,
            'confirmada'              => 1,
            'contador_confirmaciones' => $nuevoContador,
        ];
    }

    public function listSearchMissed(int $limit = 100): array
    {
        return $this->db->table('busquedas_no_encontradas')
            ->select('id, termino, termino_normalizado, contador, ultima_busqueda_at, created_at, updated_at')
            ->orderBy('contador', 'DESC')
            ->orderBy('ultima_busqueda_at', 'DESC')
            ->limit(max(1, min($limit, 500)))
            ->get()
            ->getResultArray();
    }
}
