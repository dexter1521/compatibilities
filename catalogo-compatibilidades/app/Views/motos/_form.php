<?php
$isEdit  = !empty($moto);
$action  = $isEdit
    ? site_url('/motos/' . $moto['id'] . '/update')
    : site_url('/motos/store');
$titulo  = $isEdit ? 'Editar Motocicleta' : 'Nueva Motocicleta';

$old = $old ?? [];
$v   = fn(string $k) => esc($old[$k] ?? ($moto[$k] ?? ''));
?>
<div class="modal-header" style="border-bottom:1px solid rgba(17,24,39,.08);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:800;font-size:16px;color:var(--compat-ink);">
        <i class='bx bx-car' style="color:var(--compat-accent);margin-right:6px;"></i>
        <?= $titulo ?>
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form
    id="moto-form"
    hx-post="<?= $action ?>"
    hx-target="#modal-content"
    hx-swap="innerHTML"
>
    <?= csrf_field() ?>

    <div class="modal-body" style="padding:20px;">

        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
            <i class='bx bx-error-circle'></i> <?= esc($errors['general']) ?>
        </div>
        <?php endif ?>

        <!-- MARCA -->
        <div x-data="{ nuevaMarca: <?= (!$isEdit && !empty($old['marca_nombre'])) ? 'true' : 'false' ?> }">

            <div class="form-group mb-3" x-show="!nuevaMarca">
                <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                    Marca <span style="color:#dc2626">*</span>
                </label>
                <select name="marca_id" class="form-control <?= isset($errors['marca_id']) ? 'is-invalid' : '' ?>" style="font-size:13.5px;">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($marcas as $marca): ?>
                    <option value="<?= $marca['id'] ?>" <?= ((int)($old['marca_id'] ?? $moto['marca_id'] ?? 0) === (int)$marca['id']) ? 'selected' : '' ?>>
                        <?= esc($marca['nombre']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
                <?php if (isset($errors['marca_id'])): ?>
                <div class="invalid-feedback"><?= esc($errors['marca_id']) ?></div>
                <?php endif ?>
                <small style="cursor:pointer;color:var(--compat-accent);font-size:12px;" @click="nuevaMarca = true">
                    + Agregar nueva marca
                </small>
            </div>

            <div class="form-group mb-3" x-show="nuevaMarca" x-cloak>
                <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                    Nombre de nueva marca <span style="color:#dc2626">*</span>
                </label>
                <input
                    type="text"
                    name="marca_nombre"
                    class="form-control"
                    placeholder="Ej: Honda, Yamaha, Suzuki…"
                    value="<?= esc($old['marca_nombre'] ?? '') ?>"
                    style="font-size:13.5px;"
                >
                <small style="cursor:pointer;color:#6b7280;font-size:12px;" @click="nuevaMarca = false">
                    ← Usar marca existente
                </small>
            </div>

        </div>

        <!-- MODELO -->
        <div class="form-group mb-3">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                Modelo <span style="color:#dc2626">*</span>
            </label>
            <input
                type="text"
                name="modelo"
                class="form-control <?= isset($errors['modelo']) ? 'is-invalid' : '' ?>"
                placeholder="Ej: CB125F, CG 150, FZ25…"
                value="<?= $v('modelo') ?>"
                style="font-size:13.5px;"
            >
            <?php if (isset($errors['modelo'])): ?>
            <div class="invalid-feedback"><?= esc($errors['modelo']) ?></div>
            <?php endif ?>
        </div>

        <!-- AÑOS -->
        <div class="form-row">
            <div class="form-group col-md-6 mb-3">
                <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                    Año desde
                </label>
                <input
                    type="number"
                    name="anio_desde"
                    class="form-control <?= isset($errors['anio_desde']) ? 'is-invalid' : '' ?>"
                    placeholder="2005"
                    min="1900" max="2099"
                    value="<?= $v('anio_desde') ?>"
                    style="font-size:13.5px;"
                >
                <?php if (isset($errors['anio_desde'])): ?>
                <div class="invalid-feedback"><?= esc($errors['anio_desde']) ?></div>
                <?php endif ?>
            </div>
            <div class="form-group col-md-6 mb-3">
                <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                    Año hasta
                </label>
                <input
                    type="number"
                    name="anio_hasta"
                    class="form-control <?= isset($errors['anio_hasta']) ? 'is-invalid' : '' ?>"
                    placeholder="2015"
                    min="1900" max="2099"
                    value="<?= $v('anio_hasta') ?>"
                    style="font-size:13.5px;"
                >
            </div>
        </div>

        <!-- CILINDRADA -->
        <div class="form-group mb-0">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                Cilindrada
            </label>
            <input
                type="text"
                name="cilindrada"
                class="form-control"
                placeholder="Ej: 125cc, 150cc, 250cc…"
                value="<?= $v('cilindrada') ?>"
                style="font-size:13.5px;"
            >
        </div>

    </div><!-- modal-body -->

    <div class="modal-footer" style="border-top:1px solid rgba(17,24,39,.08);padding:14px 20px;gap:8px;">
        <button type="button" class="btn btn-light" data-dismiss="modal" style="font-size:13px;">
            Cancelar
        </button>
        <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--compat-accent),var(--compat-accent-dark));color:#fff;font-size:13px;font-weight:700;border:0;border-radius:8px;padding:8px 20px;">
            <i class='bx bx-save'></i> <?= $isEdit ? 'Guardar cambios' : 'Crear moto' ?>
        </button>
    </div>

</form>
