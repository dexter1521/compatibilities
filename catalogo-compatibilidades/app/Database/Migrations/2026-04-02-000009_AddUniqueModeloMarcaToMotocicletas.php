<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueModeloMarcaToMotocicletas extends Migration
{
    public function up(): void
    {
        // Primero borrar duplicados que puedan existir (conserva el de menor id)
        $this->db->query('
            DELETE m1 FROM motocicletas m1
            INNER JOIN motocicletas m2
            ON m1.marca_id = m2.marca_id
               AND LOWER(m1.modelo) = LOWER(m2.modelo)
               AND m1.id > m2.id
        ');

        $this->forge->addUniqueKey(['marca_id', 'modelo'], 'uq_motocicletas_marca_modelo');
        $this->forge->processIndexes('motocicletas');
    }

    public function down(): void
    {
        $this->forge->dropKey('motocicletas', 'uq_motocicletas_marca_modelo');
    }
}
