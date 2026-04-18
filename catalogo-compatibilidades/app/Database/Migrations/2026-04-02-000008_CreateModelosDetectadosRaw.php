<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModelosDetectadosRaw extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'texto_detectado' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nombre_producto' => ['type' => 'TEXT'],
            'created_at'      => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('texto_detectado');
        $this->forge->createTable('modelos_detectados_raw', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('modelos_detectados_raw', true);
    }
}
