<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiposPiezaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'     => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tipos_pieza');
    }

    public function down()
    {
        $this->forge->dropTable('tipos_pieza', true);
    }
}
