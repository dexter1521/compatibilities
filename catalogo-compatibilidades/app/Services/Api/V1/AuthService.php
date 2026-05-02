<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\RefreshTokenModel;
use App\Models\UserModel;

class AuthService
{
    private UserModel $users;
    private RefreshTokenModel $refreshTokens;
    private JwtService $jwt;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->refreshTokens = new RefreshTokenModel();
        $this->jwt = new JwtService();
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->users
            ->select('users.id, users.role_id, users.nombre, users.email, users.password_hash, users.activo, roles.slug AS role_slug')
            ->join('roles', 'roles.id = users.role_id')
            ->where('LOWER(users.email)', mb_strtolower(trim($email)))
            ->first();

        if (!$user || (int) $user['activo'] !== 1) {
            return null;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        $access = $this->jwt->issueAccessToken([
            'sub' => (int) $user['id'],
            'role' => (string) $user['role_slug'],
            'email' => (string) $user['email'],
        ]);

        $refreshPlain = bin2hex(random_bytes(48));
        $hash = hash('sha256', $refreshPlain);
        $expires = date('Y-m-d H:i:s', time() + (7 * 24 * 3600));

        $this->refreshTokens->insert([
            'user_id' => (int) $user['id'],
            'token_hash' => $hash,
            'expires_at' => $expires,
            'revoked_at' => null,
        ]);

        unset($user['password_hash']);

        return [
            'user' => $user,
            'access_token' => $access,
            'refresh_token' => $refreshPlain,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
    }

    public function refresh(string $refreshPlain): ?array
    {
        $hash = hash('sha256', $refreshPlain);
        $row = $this->refreshTokens->where('token_hash', $hash)->first();
        if (!$row || $row['revoked_at'] !== null || strtotime((string) $row['expires_at']) <= time()) {
            return null;
        }

        $user = $this->users
            ->select('users.id, users.role_id, users.nombre, users.email, users.activo, roles.slug AS role_slug')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', (int) $row['user_id'])
            ->first();

        if (!$user || (int) $user['activo'] !== 1) {
            return null;
        }

        $this->refreshTokens->update((int) $row['id'], ['revoked_at' => date('Y-m-d H:i:s')]);

        $access = $this->jwt->issueAccessToken([
            'sub' => (int) $user['id'],
            'role' => (string) $user['role_slug'],
            'email' => (string) $user['email'],
        ]);

        $newRefresh = bin2hex(random_bytes(48));
        $newHash = hash('sha256', $newRefresh);
        $expires = date('Y-m-d H:i:s', time() + (7 * 24 * 3600));

        $this->refreshTokens->insert([
            'user_id' => (int) $user['id'],
            'token_hash' => $newHash,
            'expires_at' => $expires,
            'revoked_at' => null,
        ]);

        return [
            'access_token' => $access,
            'refresh_token' => $newRefresh,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
    }

    public function revokeRefreshToken(string $refreshPlain): void
    {
        $hash = hash('sha256', $refreshPlain);
        $row = $this->refreshTokens->where('token_hash', $hash)->first();
        if ($row && $row['revoked_at'] === null) {
            $this->refreshTokens->update((int) $row['id'], ['revoked_at' => date('Y-m-d H:i:s')]);
        }
    }
}
