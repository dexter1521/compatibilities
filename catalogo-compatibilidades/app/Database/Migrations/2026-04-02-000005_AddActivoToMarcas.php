<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActivoToMarcas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('marcas', [
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'slug',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('marcas', 'activo');
    }
}
