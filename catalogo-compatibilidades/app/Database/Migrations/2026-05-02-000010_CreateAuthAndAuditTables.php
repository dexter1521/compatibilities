<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthAndAuditTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 120],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug', 'uq_roles_slug');
        $this->forge->createTable('roles', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
            'email' => ['type' => 'VARCHAR', 'constraint' => 180],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email', 'uq_users_email');
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'RESTRICT', 'RESTRICT', 'fk_users_role');
        $this->forge->createTable('users', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 128],
            'expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash', 'uq_refresh_tokens_hash');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT', 'fk_refresh_user');
        $this->forge->createTable('refresh_tokens', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'metodo' => ['type' => 'VARCHAR', 'constraint' => 10],
            'ruta' => ['type' => 'VARCHAR', 'constraint' => 255],
            'status_code' => ['type' => 'SMALLINT', 'unsigned' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'RESTRICT', 'fk_audit_user');
        $this->forge->createTable('audit_logs', true);

        $now = date('Y-m-d H:i:s');
        $this->db->table('roles')->insertBatch([
            ['nombre' => 'Administrador', 'slug' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Vendedor', 'slug' => 'vendedor', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('refresh_tokens', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
    }
}
