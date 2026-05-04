<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ApiV1CatalogoEndpointsTest extends TestCase
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

    public function testMotocicletasAndPiezasValidationAndNotFound(): void
    {
        $adminToken = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $badMoto = $this->request('POST', '/api/v1/motocicletas', [
            'modelo' => 'Sin Marca',
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(422, $badMoto['status']);

        $badPieza = $this->request('POST', '/api/v1/piezas', [
            'nombre' => '',
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(422, $badPieza['status']);

        $missingMoto = $this->request('GET', '/api/v1/motocicletas/99999999', null, ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(404, $missingMoto['status']);

        $missingPieza = $this->request('GET', '/api/v1/piezas/99999999', null, ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(404, $missingPieza['status']);
    }

    public function testMarcasCrudAnd404(): void
    {
        $token = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');
        $suffix = (string) time() . (string) random_int(100, 999);
        $brand = 'Marca Caso ' . $suffix;

        $list = $this->request('GET', '/api/v1/marcas', null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $list['status']);
        $this->assertIsArray($list['json']['data']['items'] ?? null);

        $create = $this->request('POST', '/api/v1/marcas', [
            'nombre' => $brand,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(201, $create['status']);
        $marcaId = (int) ($create['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $marcaId);

        $show = $this->request('GET', '/api/v1/marcas/' . $marcaId, null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $show['status']);
        $this->assertSame($brand, $show['json']['data']['nombre'] ?? '');

        $update = $this->request('PUT', '/api/v1/marcas/' . $marcaId, [
            'nombre' => $brand . ' Editada',
            'activo' => 0,
        ], [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $update['status']);

        $delete = $this->request('DELETE', '/api/v1/marcas/' . $marcaId, null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(200, $delete['status']);

        $missing = $this->request('GET', '/api/v1/marcas/' . $marcaId, null, [
            'Authorization: Bearer ' . $token,
        ]);
        $this->assertSame(404, $missing['status']);
    }

    public function testAliasesAndCompatibilidadesFlowAnd404(): void
    {
        $adminToken = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $suffix = (string) time() . (string) random_int(100, 999);
        $marcaId = $this->createMarca('Marca Test ' . $suffix, 'marca-test-' . $suffix);

        $motoCreate = $this->request('POST', '/api/v1/motocicletas', [
            'marca_id' => $marcaId,
            'modelo' => 'Modelo ' . $suffix,
            'anio_desde' => 2020,
            'anio_hasta' => 2024,
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(201, $motoCreate['status']);
        $motoId = (int) ($motoCreate['json']['data']['id'] ?? 0);

        $piezaCreate = $this->request('POST', '/api/v1/piezas', [
            'nombre' => 'Pieza ' . $suffix,
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(201, $piezaCreate['status']);
        $piezaId = (int) ($piezaCreate['json']['data']['id'] ?? 0);

        $aliasCreate = $this->request('POST', '/api/v1/aliases', [
            'motocicleta_id' => $motoId,
            'alias' => 'Alias ' . $suffix,
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(201, $aliasCreate['status']);

        $aliasId = (int) ($aliasCreate['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $aliasId);

        $compatCreate = $this->request('POST', '/api/v1/compatibilidades', [
            'pieza_maestra_id' => $piezaId,
            'motocicleta_id' => $motoId,
            'confirmada' => 0,
        ], ['Authorization: Bearer ' . $adminToken]);
        $this->assertSame(201, $compatCreate['status']);
        $compatId = (int) ($compatCreate['json']['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $compatId);

        $compatConfirm = $this->request('PATCH', '/api/v1/compatibilidades/' . $compatId . '/confirmar', null, [
            'Authorization: Bearer ' . $adminToken,
        ]);
        $this->assertSame(200, $compatConfirm['status']);

        $aliasDelete = $this->request('DELETE', '/api/v1/aliases/' . $aliasId, null, [
            'Authorization: Bearer ' . $adminToken,
        ]);
        $this->assertSame(200, $aliasDelete['status']);

        $compatDelete = $this->request('DELETE', '/api/v1/compatibilidades/' . $compatId, null, [
            'Authorization: Bearer ' . $adminToken,
        ]);
        $this->assertSame(200, $compatDelete['status']);

        $compatMissing = $this->request('GET', '/api/v1/compatibilidades/' . $compatId, null, [
            'Authorization: Bearer ' . $adminToken,
        ]);
        $this->assertSame(404, $compatMissing['status']);
    }

    public function testForbiddenAndUnauthorizedCases(): void
    {
        $responseNoToken = $this->request('GET', '/api/v1/compatibilidades');
        $this->assertSame(401, $responseNoToken['status']);

        $suffix = (string) time() . (string) random_int(100, 999);
        $email = 'vend.catalogo.' . $suffix . '@sharkmotors.local';
        $password = 'Vendedor123!';
        $this->createVendedorUser($email, $password, 'Vendedor Catalogo ' . $suffix);

        $vendedorToken = $this->getAccessToken($email, $password);

        $forbiddenCreate = $this->request('POST', '/api/v1/piezas', [
            'nombre' => 'No debe crear',
        ], ['Authorization: Bearer ' . $vendedorToken]);
        $this->assertSame(403, $forbiddenCreate['status']);

        $forbiddenImport = $this->request('POST', '/api/v1/import/productos', null, [
            'Authorization: Bearer ' . $vendedorToken,
        ]);
        $this->assertSame(403, $forbiddenImport['status']);
    }

    public function testImportValidation422ForAdminWithoutFile(): void
    {
        $adminToken = $this->getAccessToken('admin@sharkmotors.local', 'Admin123!');

        $response = $this->request('POST', '/api/v1/import/productos', null, [
            'Authorization: Bearer ' . $adminToken,
        ]);

        $this->assertSame(422, $response['status']);
    }

    private function createMarca(string $nombre, string $slug): int
    {
        $stmt = $this->db->prepare('INSERT INTO marcas (nombre, slug, activo, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())');
        if (!$stmt) {
            $this->fail('No se pudo preparar insert marca.');
        }

        $stmt->bind_param('ss', $nombre, $slug);
        $ok = $stmt->execute();
        $id = (int) $this->db->insert_id;
        $stmt->close();

        if (!$ok || $id <= 0) {
            $this->fail('No se pudo crear marca para test.');
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

        $json = json_decode((string) $body, true);
        if (!is_array($json)) {
            $json = null;
        }

        return [
            'status' => $status,
            'body' => (string) $body,
            'json' => $json,
        ];
    }
}
