<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnrichEstadoToProductos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('productos', [
            'enrich_estado' => [
                'type'       => "ENUM('ok','sin_tipo','sin_moto','sin_ambos')",
                'null'       => true,
                'default'    => null,
                'after'      => 'pieza_maestra_id',
            ],
        ]);

        $this->db->query('ALTER TABLE productos ADD INDEX idx_productos_enrich_estado (enrich_estado)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE productos DROP INDEX idx_productos_enrich_estado');
        $this->forge->dropColumn('productos', 'enrich_estado');
    }
}
