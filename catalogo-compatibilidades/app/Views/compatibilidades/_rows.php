<?php if (empty($compatibilidades)): ?>
    <tr>
        <td colspan="6" style="text-align:center;padding:48px 0;color:#9ca3af;font-size:14px;">
            <i class='bx bx-link-alt' style="font-size:40px;display:block;margin-bottom:10px;"></i>
            No hay compatibilidades registradas todavía.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($compatibilidades as $c): ?>
        <?php
        $motoLabel = trim(($c['marca_nombre'] ?? '') . ' ' . ($c['moto_modelo'] ?? ''));
        $rowText   = strtolower($c['pieza_nombre'] . ' ' . $motoLabel);
        ?>
        <tr x-show="busq === '' || '<?= htmlspecialchars($rowText, ENT_QUOTES) ?>'.includes(busq.toLowerCase())">
            <td style="color:#d1d5db;font-size:12px;"><?= (int) $c['id'] ?></td>
            <td>
                <span style="font-weight:700;color:var(--compat-accent-dark);">
                    <i class='bx bx-wrench' style="font-size:12px;margin-right:3px;"></i>
                    <?= esc($c['pieza_nombre']) ?>
                </span>
            </td>
            <td>
                <span style="font-weight:600;"><?= esc($motoLabel) ?></span>
                <?php if ($c['anio_desde'] || $c['anio_hasta']): ?>
                    <br><span style="font-size:11.5px;color:#9ca3af;">
                        <?= esc($c['anio_desde'] ?? '?') ?>
                        <?= ($c['anio_hasta'] && $c['anio_hasta'] != $c['anio_desde']) ? '– ' . esc($c['anio_hasta']) : '' ?>
                        <?= $c['cilindrada'] ? ' · ' . esc($c['cilindrada']) : '' ?>
                    </span>
                <?php endif ?>
            </td>
            <td>
                <?php if ($c['confirmada']): ?>
                    <span class="badge-confirmada"><i class='bx bx-check-circle'></i> Confirmada</span>
                <?php else: ?>
                    <span class="badge-pendiente">Pendiente</span>
                <?php endif ?>
            </td>
            <td style="text-align:center;">
                <?php if ((int)$c['contador_confirmaciones'] > 0): ?>
                    <span style="font-weight:700;color:#15803d;"><?= (int) $c['contador_confirmaciones'] ?></span>
                <?php else: ?>
                    <span style="color:#e5e7eb;">0</span>
                <?php endif ?>
            </td>
            <td style="text-align:right;white-space:nowrap;">
                <button
                    class="btn-tbl-edit"
                    title="Editar"
                    hx-get="<?= site_url('/compatibilidades/' . $c['id'] . '/edit') ?>"
                    hx-target="#modal-content"
                    hx-swap="innerHTML"><i class='bx bx-edit'></i></button>
                <button
                    class="btn-tbl-del"
                    title="Eliminar"
                    hx-post="<?= site_url('/compatibilidades/' . $c['id'] . '/delete') ?>"
                    hx-target="#compats-tbody"
                    hx-swap="innerHTML"
                    hx-confirm="¿Eliminar la relación '<?= esc($c['pieza_nombre']) ?> — <?= esc($motoLabel) ?>'?"><i class='bx bx-trash'></i></button>
            </td>
        </tr>
    <?php endforeach ?>
<?php endif ?>