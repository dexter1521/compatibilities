<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarcaLineaToImportItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('import_items', [
            'marca' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'nombre',
            ],
            'linea' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
                'after'      => 'marca',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('import_items', ['marca', 'linea']);
    }
}
