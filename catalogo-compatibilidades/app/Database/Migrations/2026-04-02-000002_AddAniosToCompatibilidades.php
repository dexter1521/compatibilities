<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAniosToCompatibilidades extends Migration
{
    public function up()
    {
        $this->forge->addColumn('compatibilidades', [
            'anio_desde' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'motocicleta_id',
            ],
            'anio_hasta' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'anio_desde',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('compatibilidades', ['anio_desde', 'anio_hasta']);
    }
}
