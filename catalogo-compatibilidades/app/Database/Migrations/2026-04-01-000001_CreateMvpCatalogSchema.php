<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMvpCatalogSchema extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 180],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre', 'uq_marcas_nombre');
        $this->forge->addUniqueKey('slug', 'uq_marcas_slug');
        $this->forge->createTable('marcas');

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'marca_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'modelo'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'anio_desde'   => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'anio_hasta'   => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'cilindrada'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('marca_id', false, false, 'idx_motocicletas_marca_id');
        $this->forge->addUniqueKey(['marca_id', 'modelo', 'anio_desde', 'anio_hasta'], 'uq_motocicletas_base');
        $this->forge->addUniqueKey('slug', 'uq_motocicletas_slug');
        $this->forge->addForeignKey('marca_id', 'marcas', 'id', 'CASCADE', 'RESTRICT', 'fk_motocicletas_marcas');
        $this->forge->createTable('motocicletas');

        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'motocicleta_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'alias'         => ['type' => 'VARCHAR', 'constraint' => 180],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('motocicleta_id', false, false, 'idx_alias_motos_motocicleta_id');
        $this->forge->addUniqueKey('alias', 'uq_alias_motos_alias');
        $this->forge->addUniqueKey('slug', 'uq_alias_motos_slug');
        $this->forge->addForeignKey('motocicleta_id', 'motocicletas', 'id', 'CASCADE', 'RESTRICT', 'fk_alias_motos_motocicletas');
        $this->forge->createTable('alias_motos');

        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 180],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 220],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre', 'uq_piezas_maestras_nombre');
        $this->forge->addUniqueKey('slug', 'uq_piezas_maestras_slug');
        $this->forge->createTable('piezas_maestras');

        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'       => ['type' => 'VARCHAR', 'constraint' => 180],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre', 'uq_proveedores_nombre');
        $this->forge->addUniqueKey('slug', 'uq_proveedores_slug');
        $this->forge->createTable('proveedores');

        $this->forge->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'proveedor_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'pieza_maestra_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'clave_proveedor'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'nombre'           => ['type' => 'VARCHAR', 'constraint' => 220],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 240],
            'activo'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pieza_maestra_id', false, false, 'idx_productos_pieza_maestra_id');
        $this->forge->addKey('clave_proveedor', false, false, 'idx_productos_clave_proveedor');
        $this->forge->addUniqueKey(['proveedor_id', 'clave_proveedor'], 'uq_productos_proveedor_clave');
        $this->forge->addUniqueKey('slug', 'uq_productos_slug');
        $this->forge->addForeignKey('proveedor_id', 'proveedores', 'id', 'CASCADE', 'RESTRICT', 'fk_productos_proveedores');
        $this->forge->addForeignKey('pieza_maestra_id', 'piezas_maestras', 'id', 'SET NULL', 'RESTRICT', 'fk_productos_piezas_maestras');
        $this->forge->createTable('productos');

        $this->forge->addField([
            'id'                      => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'pieza_maestra_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'motocicleta_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'confirmada'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'contador_confirmaciones' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('motocicleta_id', false, false, 'idx_compatibilidades_motocicleta_id');
        $this->forge->addUniqueKey(['pieza_maestra_id', 'motocicleta_id'], 'uq_compatibilidades_par');
        $this->forge->addForeignKey('pieza_maestra_id', 'piezas_maestras', 'id', 'CASCADE', 'RESTRICT', 'fk_compat_piezas');
        $this->forge->addForeignKey('motocicleta_id', 'motocicletas', 'id', 'CASCADE', 'RESTRICT', 'fk_compat_motos');
        $this->forge->createTable('compatibilidades');

        $this->forge->addField([
            'id'                     => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'producto_origen_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'producto_equivalente_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'tipo'                   => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'equivalente'],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['producto_origen_id', 'producto_equivalente_id'], 'uq_equivalencias_par');
        $this->forge->addForeignKey('producto_origen_id', 'productos', 'id', 'CASCADE', 'RESTRICT', 'fk_equiv_producto_origen');
        $this->forge->addForeignKey('producto_equivalente_id', 'productos', 'id', 'CASCADE', 'RESTRICT', 'fk_equiv_producto_equivalente');
        $this->forge->createTable('equivalencias');

        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'termino'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'termino_normalizado' => ['type' => 'VARCHAR', 'constraint' => 255],
            'contador'            => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'ultima_busqueda_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('termino_normalizado', 'uq_busquedas_no_encontradas_termino');
        $this->forge->createTable('busquedas_no_encontradas');

        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'archivo_nombre' => ['type' => 'VARCHAR', 'constraint' => 255],
            'estado'         => ['type' => 'ENUM', 'constraint' => ['pendiente', 'procesando', 'finalizado', 'error'], 'default' => 'pendiente'],
            'total_items'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'procesados'     => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'errores'        => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'iniciado_en'    => ['type' => 'DATETIME', 'null' => true],
            'finalizado_en'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('import_jobs');

        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'import_job_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'fila_numero'    => ['type' => 'INT', 'unsigned' => true],
            'proveedor'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'clave_proveedor' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nombre'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'estado'         => ['type' => 'ENUM', 'constraint' => ['pendiente', 'procesado', 'error'], 'default' => 'pendiente'],
            'mensaje_error'  => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clave_proveedor', false, false, 'idx_import_items_clave');
        $this->forge->addUniqueKey(['import_job_id', 'fila_numero'], 'uq_import_items_fila');
        $this->forge->addForeignKey('import_job_id', 'import_jobs', 'id', 'CASCADE', 'RESTRICT', 'fk_import_items_jobs');
        $this->forge->createTable('import_items');
    }

    public function down()
    {
        $this->forge->dropTable('import_items', true);
        $this->forge->dropTable('import_jobs', true);
        $this->forge->dropTable('busquedas_no_encontradas', true);
        $this->forge->dropTable('equivalencias', true);
        $this->forge->dropTable('compatibilidades', true);
        $this->forge->dropTable('productos', true);
        $this->forge->dropTable('proveedores', true);
        $this->forge->dropTable('piezas_maestras', true);
        $this->forge->dropTable('alias_motos', true);
        $this->forge->dropTable('motocicletas', true);
        $this->forge->dropTable('marcas', true);
    }
}
