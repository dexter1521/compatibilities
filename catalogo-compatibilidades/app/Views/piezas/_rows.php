<?php if (empty($piezas)): ?>
    <tr>
        <td colspan="3" style="text-align:center;padding:48px 0;color:#9ca3af;font-size:14px;">
            <i class='bx bx-wrench' style="font-size:40px;display:block;margin-bottom:10px;"></i>
            No hay piezas maestras registradas todavía.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($piezas as $p): ?>
        <tr x-show="busq === '' || '<?= strtolower(esc($p['nombre'])) ?>'.includes(busq.toLowerCase())">
            <td style="color:#d1d5db;font-size:12px;"><?= (int) $p['id'] ?></td>
            <td style="font-weight:600;"><?= esc($p['nombre']) ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <button
                    class="btn-tbl-edit"
                    title="Editar"
                    hx-get="<?= site_url('/piezas/' . $p['id'] . '/edit') ?>"
                    hx-target="#modal-content"
                    hx-swap="innerHTML"><i class='bx bx-edit'></i></button>
                <button
                    class="btn-tbl-del"
                    title="Eliminar"
                    hx-post="<?= site_url('/piezas/' . $p['id'] . '/delete') ?>"
                    hx-target="#piezas-tbody"
                    hx-swap="innerHTML"
                    hx-confirm="¿Eliminar la pieza '<?= esc($p['nombre']) ?>'?"><i class='bx bx-trash'></i></button>
            </td>
        </tr>
    <?php endforeach ?>
<?php endif ?>