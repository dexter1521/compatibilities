<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ApiV1AuthFlowTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim(getenv('API_TEST_BASE_URL') ?: 'http://localhost:8080', '/');
    }

    public function testProtectedEndpointRequiresToken(): void
    {
        $response = $this->request('GET', '/api/v1/productos');

        $this->assertSame(401, $response['status']);
        $this->assertFalse((bool) ($response['json']['success'] ?? true));
    }

    public function testLoginAndMeFlow(): void
    {
        $login = $this->request('POST', '/api/v1/auth/login', [
            'email' => 'admin@sharkmotors.local',
            'password' => 'Admin123!',
        ]);

        $this->assertSame(200, $login['status']);
        $this->assertTrue((bool) ($login['json']['success'] ?? false));

        $accessToken = (string) ($login['json']['data']['access_token'] ?? '');
        $this->assertNotSame('', $accessToken);

        $me = $this->request('GET', '/api/v1/auth/me', null, [
            'Authorization: Bearer ' . $accessToken,
        ]);

        $this->assertSame(200, $me['status']);
        $this->assertTrue((bool) ($me['json']['success'] ?? false));
        $this->assertSame('admin@sharkmotors.local', $me['json']['data']['email'] ?? null);
        $this->assertSame('admin', $me['json']['data']['role'] ?? null);
    }

    public function testAdminEndpointAccessibleWithToken(): void
    {
        $token = $this->getAccessToken();

        $response = $this->request('GET', '/api/v1/compatibilidades?per_page=1&page=1', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertSame(200, $response['status']);
        $this->assertTrue((bool) ($response['json']['success'] ?? false));
    }

    public function testAuthRateLimitReturns429(): void
    {
        $saw429 = false;

        for ($i = 0; $i < 20; $i++) {
            $response = $this->request('POST', '/api/v1/auth/login', [
                'email' => 'admin@sharkmotors.local',
                'password' => 'password-invalido',
            ]);

            if ($response['status'] === 429) {
                $saw429 = true;
                break;
            }
        }

        $this->assertTrue($saw429, 'No se obtuvo 429 en auth/login dentro de 20 intentos.');
    }

    private function getAccessToken(): string
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'email' => 'admin@sharkmotors.local',
            'password' => 'Admin123!',
        ]);

        $this->assertSame(200, $response['status'], 'No se pudo autenticar para obtener token.');

        $token = (string) ($response['json']['data']['access_token'] ?? '');
        $this->assertNotSame('', $token, 'Login sin access_token.');

        return $token;
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
