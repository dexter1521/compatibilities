<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            ['nombre' => 'Administrador', 'slug' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Vendedor', 'slug' => 'vendedor', 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('roles')->ignore(true)->insertBatch($roles);

        $adminRole = $this->db->table('roles')->where('slug', 'admin')->get()->getRowArray();
        if (!$adminRole) {
            throw new \RuntimeException('No fue posible preparar el rol admin.');
        }

        $email = getenv('ADMIN_EMAIL') ?: 'admin@sharkmotors.local';
        $password = getenv('ADMIN_PASSWORD') ?: 'Admin123!';
        $nombre = getenv('ADMIN_NAME') ?: 'Administrador API';

        $exists = $this->db->table('users')->where('LOWER(email)', mb_strtolower($email))->get()->getRowArray();
        if ($exists) {
            $this->db->table('users')->where('id', $exists['id'])->update([
                'role_id' => (int) $adminRole['id'],
                'nombre' => $nombre,
                'activo' => 1,
                'updated_at' => $now,
            ]);

            echo "Usuario admin ya existía, actualizado: {$email}" . PHP_EOL;
            return;
        }

        $this->db->table('users')->insert([
            'role_id' => (int) $adminRole['id'],
            'nombre' => $nombre,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'activo' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "Usuario admin creado: {$email}" . PHP_EOL;
        echo "Password temporal: {$password}" . PHP_EOL;
        echo "Cámbiala en cuanto inicies sesión." . PHP_EOL;
    }
}
