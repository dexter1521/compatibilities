<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandProductosNombreSlug extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('productos', [
            'nombre' => [
                'name'       => 'nombre',
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'slug' => [
                'name'       => 'slug',
                'type'       => 'VARCHAR',
                'constraint' => 520,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('productos', [
            'nombre' => [
                'name'       => 'nombre',
                'type'       => 'VARCHAR',
                'constraint' => 220,
                'null'       => false,
            ],
            'slug' => [
                'name'       => 'slug',
                'type'       => 'VARCHAR',
                'constraint' => 240,
                'null'       => false,
            ],
        ]);
    }
}
