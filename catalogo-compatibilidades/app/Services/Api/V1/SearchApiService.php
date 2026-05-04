<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\SearchModel;
use CodeIgniter\Database\BaseConnection;

class SearchApiService
{
    private SearchModel $searchModel;
    private BaseConnection $db;
    private string $timezone;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
        $this->db = \Config\Database::connect();
        $this->timezone = config('App')->appTimezone ?: 'UTC';
    }

    public function search(string $term, int $limit = 50, int $page = 1): array
    {
        $page = max(1, $page);
        $limit = max(1, min($limit, 50));

        $allItems = $this->searchModel->searchByTerm($term);
        $total = count($allItems);
        $offset = ($page - 1) * $limit;

        $items = array_slice($allItems, $offset, $limit);

        $items = array_map(function (array $item): array {
            $item['compatibilidades'] = array_map(function (array $compat): array {
                $compat['score_relevancia'] = (int) ($compat['contador_confirmaciones'] ?? 0);
                return $compat;
            }, $item['compatibilidades'] ?? []);

            return $item;
        }, $items);

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'last_page' => (int) ceil($total / $limit),
                'sort_by' => 'relevancia',
                'sort_dir' => 'desc',
                'filters' => [
                    'q' => $term !== '' ? $term : null,
                ],
                'timezone' => $this->timezone,
            ],
        ];
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
            'score_relevancia'        => $nuevoContador,
            'updated_at'              => $this->formatDateTime(date('Y-m-d H:i:s')),
        ];
    }

    public function listSearchMissed(array $query = []): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, min((int) ($query['per_page'] ?? 50), 200));
        $offset = ($page - 1) * $perPage;
        $sortBy = (string) ($query['sort_by'] ?? 'contador');
        $sortDir = strtoupper((string) ($query['sort_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $q = trim((string) ($query['q'] ?? ''));

        $sortMap = [
            'contador' => 'contador',
            'ultima_busqueda_at' => 'ultima_busqueda_at',
            'created_at' => 'created_at',
        ];
        $orderColumn = $sortMap[$sortBy] ?? $sortMap['contador'];

        $builder = $this->db->table('busquedas_no_encontradas')
            ->select('id, termino, termino_normalizado, contador, ultima_busqueda_at, created_at, updated_at')
            ->orderBy($orderColumn, $sortDir);

        if ($q !== '') {
            $builder->groupStart()
                ->like('termino', $q)
                ->orLike('termino_normalizado', mb_strtolower($q))
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults();

        $items = $builder
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $items = array_map(function (array $row): array {
            $row['ultima_busqueda_at'] = $this->formatDateTime($row['ultima_busqueda_at'] ?? null);
            $row['created_at'] = $this->formatDateTime($row['created_at'] ?? null);
            $row['updated_at'] = $this->formatDateTime($row['updated_at'] ?? null);

            return $row;
        }, $items);

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'sort_by' => $sortBy,
                'sort_dir' => strtolower($sortDir),
                'filters' => [
                    'q' => $q !== '' ? $q : null,
                ],
                'timezone' => $this->timezone,
            ],
        ];
    }

    private function formatDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $dt = new \DateTimeImmutable($value, new \DateTimeZone($this->timezone));

        return $dt->format(\DateTimeInterface::ATOM);
    }
}
