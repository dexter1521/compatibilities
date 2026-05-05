<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ApiV1ProductosSearchTest extends TestCase
{
    private string $baseUrl;
    private mysqli $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseUrl = rtrim(getenv('API_TEST_BASE_URL') ?: 'http://localhost:8080', '/');

        $host = getenv('DB_HOST') ?: 'db';
        $user = getenv('DB_USER') ?: 'compat';
        $pass = getenv('DB_PASS') ?: 'compat123';
        $name = getenv('DB_NAME') ?: 'compatibilidades';
        $port = (int) (getenv('DB_PORT') ?: 3306);

        $this->db = new mysqli($host, $user, $pass, $name, $port);
        if ($this->db->connect_error) {
            $this->fail('No se pudo conectar a BD de pruebas: ' . $this->db->connect_error);
        }

        $this->db->set_charset('utf8mb4');
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }

        parent::tearDown();
    }

    public function testProductosCrudAnd404(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $suffix = (string) time() . (string) random_int(100, 999);
        $proveedorId = $this->createProveedor('Proveedor Test ' . $suffix, 'proveedor-test-' . $suffix);

        $create = $this->request('POST', '/api/v1/productos', [
            'proveedor_id' => $proveedorId,
            'clave_proveedor' => 'CLAVE-' . $suffix,
            'nombre' => 'Producto Prueba ' . $suffix,
            'activo' => 1,
        ], ['Authorization: Bearer ' . $token]);

        $this->assertSame(201, $create['status']);
        $this->assertTrue((bool) ($create['json']['success'] ?? false));

        $productoId = (int) ($create['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $productoId);

        $show = $this->request('GET', '/api/v1/productos/' . $productoId, null, ['Authorization: Bearer ' . $token]);
        $this->assertSame(200, $show['status']);

        $update = $this->request('PUT', '/api/v1/productos/' . $productoId, [
            'nombre' => 'Producto Prueba Editado ' . $suffix,
            'activo' => 0,
        ], ['Authorization: Bearer ' . $token]);
        $this->assertSame(200, $update['status']);

        $delete = $this->request('DELETE', '/api/v1/productos/' . $productoId, null, ['Authorization: Bearer ' . $token]);
        $this->assertSame(200, $delete['status']);

        $notFound = $this->request('GET', '/api/v1/productos/' . $productoId, null, ['Authorization: Bearer ' . $token]);
        $this->assertSame(404, $notFound['status']);
    }

    public function testProductosValidation422BySortBy(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('GET', '/api/v1/productos?sort_by=no_permitido', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    public function testSearchValidation422ByShortQuery(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('GET', '/api/v1/search?q=a', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    public function testSearchSuccessReturnsUniformMeta(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('GET', '/api/v1/search?q=ft150&limit=5&page=1', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(200, $response['status']);
        $this->assertSame('Búsqueda completada.', $response['json']['message'] ?? '');
        $this->assertArrayHasKey('items', $response['json']['data'] ?? []);
        $this->assertArrayHasKey('meta', $response['json']['data'] ?? []);
        $meta = $response['json']['data']['meta'];
        $this->assertSame(1, $meta['page']);
        $this->assertSame(5, $meta['per_page']);
        $this->assertSame('relevancia', $meta['sort_by']);
        $this->assertSame('desc', $meta['sort_dir']);
        $this->assertArrayHasKey('timezone', $meta);
        $this->assertArrayHasKey('filters', $meta);
        $this->assertTrue(array_key_exists('q', $meta['filters']));
    }

    public function testSearchValidation422ByInvalidLimit(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('GET', '/api/v1/search?q=balanceo&limit=999', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    public function testSearchMissedForbiddenForVendedor(): void
    {
        $suffix = (string) time() . (string) random_int(100, 999);
        $email = 'vendedor.' . $suffix . '@sharkmotors.local';
        $password = 'Vendedor123!';

        $this->createVendedorUser($email, $password, 'Vendedor ' . $suffix);

        $token = $this->getAccessToken($email, $password);

        $response = $this->request('GET', '/api/v1/search-missed', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(403, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    public function testSearchMissedReturnsMetaAndSortValidation(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('GET', '/api/v1/search-missed?page=1&per_page=10&sort_by=contador&sort_dir=asc', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(200, $response['status']);
        $this->assertArrayHasKey('items', $response['json']['data'] ?? []);
        $this->assertArrayHasKey('meta', $response['json']['data'] ?? []);

        $meta = $response['json']['data']['meta'];
        $this->assertSame(1, $meta['page']);
        $this->assertSame(10, $meta['per_page']);
        $this->assertSame('contador', $meta['sort_by']);
        $this->assertSame('asc', $meta['sort_dir']);

        $badSort = $this->request('GET', '/api/v1/search-missed?sort_by=wrong', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(422, $badSort['status']);
        $this->assertFalse((bool) ($badSort['json']['success'] ?? true));
    }

    public function testSearchMissedReturns422ForInvalidPageAndPerPage(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $badPage = $this->request('GET', '/api/v1/search-missed?page=0', null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(422, $badPage['status']);
        $this->assertFalse((bool) ($badPage['json']['success'] ?? true));

        $badPerPage = $this->request('GET', '/api/v1/search-missed?per_page=0', null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(422, $badPerPage['status']);
        $this->assertFalse((bool) ($badPerPage['json']['success'] ?? true));

        $badPerPageHigh = $this->request('GET', '/api/v1/search-missed?per_page=201', null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(422, $badPerPageHigh['status']);
        $this->assertFalse((bool) ($badPerPageHigh['json']['success'] ?? true));
    }

    public function testSearchMotoEndpointReturnsCompatiblesForMotoQuery(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $suffix = (string) time() . (string) random_int(100, 999);
        $marcaId = $this->createMarca('Marca Moto ' . $suffix, 'marca-moto-' . $suffix);
        $proveedorId = $this->createProveedor('Proveedor Moto ' . $suffix, 'proveedor-moto-' . $suffix);

        $motoResponse = $this->request('POST', '/api/v1/motocicletas', [
            'marca_id' => $marcaId,
            'modelo' => 'CS125 ' . $suffix,
            'anio_desde' => 2020,
            'anio_hasta' => 2024,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $motoResponse['status']);
        $motoId = (int) ($motoResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $motoId);

        $piezaResponse = $this->request('POST', '/api/v1/piezas', [
            'nombre' => 'Pieza Moto ' . $suffix,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $piezaResponse['status']);
        $piezaId = (int) ($piezaResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $piezaId);

        $productoResponse = $this->request('POST', '/api/v1/productos', [
            'proveedor_id' => $proveedorId,
            'clave_proveedor' => 'CS-PRD-' . $suffix,
            'nombre' => 'Producto CS125 ' . $suffix,
            'activo' => 1,
            'pieza_maestra_id' => $piezaId,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $productoResponse['status']);

        $compatCreate = $this->request('POST', '/api/v1/compatibilidades', [
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => 0,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $compatCreate['status']);

        $searchMoto = $this->request('GET', '/api/v1/search/moto?q=CS125 ' . $suffix . '&per_page=10', null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $searchMoto['status']);
        $this->assertArrayHasKey('items', $searchMoto['json']['data'] ?? []);

        $items = $searchMoto['json']['data']['items'];
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertArrayHasKey('moto', $items[0]);
        $this->assertSame($motoId, (int) ($items[0]['moto']['moto_id'] ?? 0));
        $this->assertArrayHasKey('piezas', $items[0]);
        $this->assertCount(1, $items[0]['piezas']);
        $this->assertSame($piezaId, (int) ($items[0]['piezas'][0]['pieza_maestra_id'] ?? 0));
    }

    public function testSearchMotoEndpointMatchesNormalizedAliases(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $suffix = (string) time() . (string) random_int(100, 999);
        $marcaId = $this->createMarca('Marca Aliases ' . $suffix, 'marca-alias-' . $suffix);
        $proveedorId = $this->createProveedor('Proveedor Alias ' . $suffix, 'proveedor-alias-' . $suffix);

        $motoResponse = $this->request('POST', '/api/v1/motocicletas', [
            'marca_id' => $marcaId,
            'modelo' => 'CS125',
            'anio_desde' => 2019,
            'anio_hasta' => 2024,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $motoResponse['status']);
        $motoId = (int) ($motoResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $motoId);

        $piezaResponse = $this->request('POST', '/api/v1/piezas', [
            'nombre' => 'Pieza Alias ' . $suffix,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $piezaResponse['status']);
        $piezaId = (int) ($piezaResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $piezaId);

        $productoResponse = $this->request('POST', '/api/v1/productos', [
            'proveedor_id' => $proveedorId,
            'clave_proveedor' => 'ALIAS-' . $suffix,
            'nombre' => 'Producto Alias Moto ' . $suffix,
            'activo' => 1,
            'pieza_maestra_id' => $piezaId,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $productoResponse['status']);

        $compatCreate = $this->request('POST', '/api/v1/compatibilidades', [
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => 1,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $compatCreate['status']);

        $variants = ['CS-125', 'CS 125'];
        foreach ($variants as $query) {
            $searchMoto = $this->request('GET', '/api/v1/search/moto?q=' . urlencode($query) . '&per_page=10', null, [
                'Authorization: Bearer ' . $token,
            ]);
            $this->assertSame(200, $searchMoto['status']);
            $this->assertArrayHasKey('items', $searchMoto['json']['data'] ?? []);

            $items = $searchMoto['json']['data']['items'];
            $this->assertIsArray($items);
            $this->assertCount(1, $items);
            $this->assertSame($motoId, (int) ($items[0]['moto']['moto_id'] ?? 0));
            $this->assertArrayHasKey('piezas', $items[0]);
            $this->assertCount(1, $items[0]['piezas']);
            $this->assertSame($piezaId, (int) ($items[0]['piezas'][0]['pieza_maestra_id'] ?? 0));
        }
    }

    public function testSearchProductoEndpointReturnsCompatiblesForProductoQuery(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $suffix = (string) time() . (string) random_int(100, 999);
        $marcaId = $this->createMarca('Marca Prod ' . $suffix, 'marca-prod-' . $suffix);
        $proveedorId = $this->createProveedor('Proveedor Prod ' . $suffix, 'proveedor-prod-' . $suffix);

        $motoResponse = $this->request('POST', '/api/v1/motocicletas', [
            'marca_id' => $marcaId,
            'modelo' => 'MX ' . $suffix,
            'anio_desde' => 2022,
            'anio_hasta' => 2022,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $motoResponse['status']);
        $motoId = (int) ($motoResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $motoId);

        $piezaResponse = $this->request('POST', '/api/v1/piezas', [
            'nombre' => 'Pieza Prod ' . $suffix,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $piezaResponse['status']);
        $piezaId = (int) ($piezaResponse['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $piezaId);

        $productClave = '049RTE-EST';
        $productoResponse = $this->request('POST', '/api/v1/productos', [
            'proveedor_id' => $proveedorId,
            'clave_proveedor' => $productClave,
            'nombre' => 'ESTATOR 8 BOBINAS MOTONETA',
            'activo' => 1,
            'pieza_maestra_id' => $piezaId,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $productoResponse['status']);

        $compatCreate = $this->request('POST', '/api/v1/compatibilidades', [
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => 1,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $compatCreate['status']);

        $searchProducto = $this->request('GET', '/api/v1/search/producto?q=' . urlencode($productClave), null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $searchProducto['status']);
        $this->assertArrayHasKey('items', $searchProducto['json']['data'] ?? []);

        $items = $searchProducto['json']['data']['items'];
        $this->assertIsArray($items);
        $this->assertGreaterThan(0, count($items));

        $match = null;
        foreach ($items as $item) {
            if (($item['clave_proveedor'] ?? '') === $productClave) {
                $match = $item;
                break;
            }
        }

        $this->assertNotNull($match);
        $this->assertArrayHasKey('compatibilidades', $match);
        $this->assertIsArray($match['compatibilidades']);
        $this->assertGreaterThan(0, count($match['compatibilidades']));
    }

    public function testImportProductosCsvReturnsJobResult(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');
        $suffix = (string) time() . (string) random_int(100, 999);
        $tmpFile = $this->buildSearchImportCsv($suffix);

        try {
            $response = $this->requestFile('POST', '/api/v1/import/productos', $tmpFile, [
                'Authorization: Bearer ' . $token,
            ]);

            $this->assertSame(201, $response['status']);
            $this->assertTrue((bool) ($response['json']['success'] ?? false));
            $this->assertArrayHasKey('data', $response['json']);

            $data = $response['json']['data'];
            $this->assertSame(1, $data['total_items']);
            $this->assertSame(1, $data['procesados']);
            $this->assertSame(0, $data['errores']);
            $this->assertSame('finalizado', $data['estado']);
            $this->assertArrayHasKey('job_id', $data);
            $this->assertIsInt((int) $data['job_id']);
        } finally {
            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        }
    }

    public function testImportValidation422WithoutFileAndValidationCodes(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('POST', '/api/v1/import/productos', null, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    private function createProveedor(string $nombre, string $slug): int
    {
        $stmt = $this->db->prepare('INSERT INTO proveedores (nombre, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        if (!$stmt) {
            $this->fail('No se pudo preparar insert proveedor.');
        }

        $stmt->bind_param('ss', $nombre, $slug);
        $ok = $stmt->execute();
        $id = (int) $this->db->insert_id;
        $stmt->close();

        if (!$ok || $id <= 0) {
            $this->fail('No se pudo crear proveedor para test.');
        }

        return $id;
    }

    private function createVendedorUser(string $email, string $password, string $nombre): void
    {
        $result = $this->db->query("SELECT id FROM roles WHERE slug = 'vendedor' LIMIT 1");
        $row = $result ? $result->fetch_assoc() : null;
        if ($result) {
            $result->free();
        }

        if (!$row || empty($row['id'])) {
            $this->fail('No existe rol vendedor para pruebas.');
        }

        $roleId = (int) $row['id'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('INSERT INTO users (role_id, nombre, email, password_hash, activo, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
        if (!$stmt) {
            $this->fail('No se pudo preparar insert usuario vendedor.');
        }

        $stmt->bind_param('isss', $roleId, $nombre, $email, $hash);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            $this->fail('No se pudo crear usuario vendedor para test.');
        }
    }

    private function getAccessToken(string $email, string $password): string
    {
        $response = $this->loginWithRetry($email, $password);

        $this->assertSame(200, $response['status'], 'No se pudo autenticar para obtener token.');

        $token = (string) ($response['json']['data']['access_token'] ?? '');
        $this->assertNotSame('', $token, 'Respuesta sin access_token.');

        return $token;
    }

    /**
     * @return array{status:int,body:string,json:array<string,mixed>|null}
     */
    private function loginWithRetry(string $email, string $password): array
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response['status'] === 429) {
            sleep(65);
            $response = $this->request('POST', '/api/v1/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);
        }

        return $response;
    }

    /**
     * @param array<string,mixed>|null $payload
     * @param list<string> $headers
     * @return array{status:int,body:string,json:array<string,mixed>|null}
     */
    private function request(string $method, string $path, ?array $payload = null, array $headers = []): array
    {
        $ch = curl_init();

        $baseHeaders = ['Accept: application/json'];
        if ($payload !== null) {
            $baseHeaders[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($baseHeaders, $headers),
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->fail('Error CURL: ' . $error);
        }

        curl_close($ch);

        $json = json_decode($body, true);
        if (!is_array($json)) {
            $json = null;
        }

        return [
            'status' => $status,
            'body' => $body,
            'json' => $json,
        ];
    }

    private function requestFile(string $method, string $path, string $filePath, array $headers = []): array
    {
        $ch = curl_init();

        $payload = ['archivo' => new \CURLFile($filePath, 'text/csv')];

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->fail('Error CURL: ' . $error);
        }

        curl_close($ch);

        $json = json_decode($body, true);
        if (!is_array($json)) {
            $json = null;
        }

        return [
            'status' => $status,
            'body' => $body,
            'json' => $json,
        ];
    }

    private function buildSearchImportCsv(string $suffix): string
    {
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'import_productos_' . $suffix . '.csv';
        $content = implode(
            "\n",
            [
                'proveedor,clave_proveedor,nombre',
                "Prueba Import $suffix, CL-$suffix, Producto Import $suffix",
            ]
        );
        file_put_contents($tmpFile, $content);
        return $tmpFile;
    }
}
