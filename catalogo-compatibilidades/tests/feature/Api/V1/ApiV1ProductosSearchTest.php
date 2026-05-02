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
}
