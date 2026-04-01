<?php
$isEdit = !empty($compat);
$action = $isEdit
    ? site_url('/compatibilidades/' . $compat['id'] . '/update')
    : site_url('/compatibilidades/store');
$titulo = $isEdit ? 'Editar Compatibilidad' : 'Nueva Compatibilidad';
$old    = $old ?? [];
$selPieza = (int)($old['pieza_maestra_id'] ?? $compat['pieza_maestra_id'] ?? 0);
$selMoto  = (int)($old['motocicleta_id']   ?? $compat['motocicleta_id']   ?? 0);
?>
<div class="modal-header" style="border-bottom:1px solid rgba(17,24,39,.08);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:800;font-size:16px;color:var(--compat-ink);">
        <i class='bx bx-link-alt' style="color:var(--compat-accent);margin-right:6px;"></i>
        <?= $titulo ?>
    </h5>
    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<form id="compat-form" hx-post="<?= $action ?>" hx-target="#modal-content" hx-swap="innerHTML">
    <?= csrf_field() ?>

    <div class="modal-body" style="padding:20px;">

        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
            <i class='bx bx-error-circle'></i> <?= esc($errors['general']) ?>
        </div>
        <?php endif ?>

        <!-- PIEZA MAESTRA -->
        <div class="form-group mb-3">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                Pieza Maestra <span style="color:#dc2626">*</span>
            </label>
            <select
                name="pieza_maestra_id"
                class="form-control <?= !empty($errors['pieza_maestra_id']) ? 'is-invalid' : '' ?>"
                style="font-size:13.5px;"
            >
                <option value="">— Selecciona una pieza —</option>
                <?php foreach ($piezas as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $selPieza === (int)$p['id'] ? 'selected' : '' ?>>
                    <?= esc($p['nombre']) ?>
                </option>
                <?php endforeach ?>
            </select>
            <?php if (!empty($errors['pieza_maestra_id'])): ?>
            <div class="invalid-feedback"><?= esc($errors['pieza_maestra_id']) ?></div>
            <?php endif ?>
        </div>

        <!-- MOTOCICLETA -->
        <div class="form-group mb-0">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                Motocicleta <span style="color:#dc2626">*</span>
            </label>
            <select
                name="motocicleta_id"
                class="form-control <?= !empty($errors['motocicleta_id']) ? 'is-invalid' : '' ?>"
                style="font-size:13.5px;"
            >
                <option value="">— Selecciona una moto —</option>
                <?php
                $currentMarca = '';
                foreach ($motos as $mo):
                    if ($currentMarca !== $mo['marca_nombre']):
                        if ($currentMarca !== '') echo '</optgroup>';
                        $currentMarca = $mo['marca_nombre'];
                        echo '<optgroup label="' . esc($currentMarca) . '">';
                    endif;
                    $label = esc($mo['modelo']);
                    if ($mo['anio_desde']) {
                        $label .= ' (' . $mo['anio_desde'];
                        if ($mo['anio_hasta'] && $mo['anio_hasta'] != $mo['anio_desde']) $label .= '–' . $mo['anio_hasta'];
                        $label .= ')';
                    }
                    if ($mo['cilindrada']) $label .= ' ' . esc($mo['cilindrada']);
                    $selected = $selMoto === (int) $mo['id'] ? 'selected' : '';
                    echo "<option value=\"{$mo['id']}\" {$selected}>{$label}</option>";
                endforeach;
                if ($currentMarca !== '') echo '</optgroup>';
                ?>
            </select>
            <?php if (!empty($errors['motocicleta_id'])): ?>
            <div class="invalid-feedback"><?= esc($errors['motocicleta_id']) ?></div>
            <?php endif ?>
            <small style="color:#9ca3af;font-size:11.5px;margin-top:4px;display:block;">
                Las motos están agrupadas por marca.
            </small>
        </div>
    </div>

    <div class="modal-footer" style="border-top:1px solid rgba(17,24,39,.08);padding:14px 20px;gap:8px;">
        <button type="button" class="btn btn-light" data-dismiss="modal" style="font-size:13px;">Cancelar</button>
        <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--compat-accent),var(--compat-accent-dark));color:#fff;font-size:13px;font-weight:700;border:0;border-radius:8px;padding:8px 20px;">
            <i class='bx bx-save'></i> <?= $isEdit ? 'Guardar cambios' : 'Crear relación' ?>
        </button>
    </div>
</form>
