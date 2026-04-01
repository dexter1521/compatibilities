<?php
// Partial para las filas del tbody de motocicletas.
// Usado por: delete() (respuesta directa a hx-target="#motos-tbody")
// y por el include inicial en motos/index.php
?>
<?php if (empty($motos)): ?>
<tr>
    <td colspan="6" class="empty-state">
        <i class='bx bx-car'></i>
        No hay motocicletas registradas todavía.
    </td>
</tr>
<?php else: ?>
<?php foreach ($motos as $m): ?>
<tr x-show="busq === '' || '<?= strtolower(esc($m['marca_nombre'])) ?> <?= strtolower(esc($m['modelo'])) ?>'.includes(busq.toLowerCase())">
    <td style="color:#d1d5db;font-size:12px;"><?= (int) $m['id'] ?></td>
    <td class="td-marca"><?= esc($m['marca_nombre']) ?></td>
    <td class="td-modelo"><?= esc($m['modelo']) ?></td>
    <td class="td-years">
        <?php if ($m['anio_desde'] || $m['anio_hasta']): ?>
            <?= esc($m['anio_desde'] ?? '?') ?>
            <?= ($m['anio_hasta'] && $m['anio_hasta'] != $m['anio_desde']) ? '– ' . esc($m['anio_hasta']) : '' ?>
        <?php else: ?>
            <span style="color:#e5e7eb">—</span>
        <?php endif ?>
    </td>
    <td class="td-cc"><?= $m['cilindrada'] ? esc($m['cilindrada']) : '<span style="color:#e5e7eb">—</span>' ?></td>
    <td style="text-align:right;white-space:nowrap;">
        <button
            class="btn-tbl-edit"
            title="Editar"
            hx-get="<?= site_url('/motos/' . $m['id'] . '/edit') ?>"
            hx-target="#modal-content"
            hx-swap="innerHTML"
        ><i class='bx bx-edit'></i></button>
        <button
            class="btn-tbl-del"
            title="Eliminar"
            hx-post="<?= site_url('/motos/' . $m['id'] . '/delete') ?>"
            hx-target="#motos-tbody"
            hx-swap="innerHTML"
            hx-confirm="¿Eliminar la moto '<?= esc($m['marca_nombre']) ?> <?= esc($m['modelo']) ?>'?"
        ><i class='bx bx-trash'></i></button>
    </td>
</tr>
<?php endforeach ?>
<?php endif ?>
