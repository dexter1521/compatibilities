<?php if (empty($marcas)): ?>
    <tr>
        <td colspan="4" style="text-align:center;padding:48px 0;color:#9ca3af;font-size:14px;">
            <i class='bx bx-tag-alt' style="font-size:40px;display:block;margin-bottom:10px;"></i>
            No hay marcas registradas todavía.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($marcas as $m): ?>
        <tr x-show="busq === '' || '<?= strtolower(esc($m['nombre'])) ?>'.includes(busq.toLowerCase())">
            <td style="color:#d1d5db;font-size:12px;"><?= (int) $m['id'] ?></td>
            <td style="font-weight:600;"><?= esc($m['nombre']) ?></td>
            <td style="text-align:center;">
                <button
                    class="btn-tbl-toggle"
                    title="<?= $m['activo'] ? 'Desactivar' : 'Activar' ?>"
                    hx-post="<?= site_url('/marcas/' . $m['id'] . '/toggle') ?>"
                    hx-target="#marcas-tbody"
                    hx-swap="innerHTML"
                    style="<?= $m['activo']
                        ? 'background:rgba(22,163,74,.1);color:#16a34a;'
                        : 'background:rgba(107,114,128,.1);color:#6b7280;' ?>
                           border:0;border-radius:7px;padding:4px 10px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                    <i class='bx <?= $m['activo'] ? 'bx-check-circle' : 'bx-minus-circle' ?>'></i>
                    <?= $m['activo'] ? 'Activo' : 'Inactivo' ?>
                </button>
            </td>
            <td style="text-align:right;white-space:nowrap;">
                <button
                    class="btn-tbl-edit"
                    title="Editar"
                    hx-get="<?= site_url('/marcas/' . $m['id'] . '/edit') ?>"
                    hx-target="#modal-content"
                    hx-swap="innerHTML"><i class='bx bx-edit'></i></button>
                <button
                    class="btn-tbl-del"
                    title="Eliminar"
                    hx-post="<?= site_url('/marcas/' . $m['id'] . '/delete') ?>"
                    hx-target="#marcas-tbody"
                    hx-swap="innerHTML"
                    hx-confirm="¿Eliminar la marca '<?= esc($m['nombre']) ?>'?"><i class='bx bx-trash'></i></button>
            </td>
        </tr>
    <?php endforeach ?>
<?php endif ?>