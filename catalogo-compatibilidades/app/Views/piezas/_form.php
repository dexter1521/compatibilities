<?php
$isEdit = !empty($pieza);
$action = $isEdit
    ? site_url('/piezas/' . $pieza['id'] . '/update')
    : site_url('/piezas/store');
$titulo = $isEdit ? 'Editar Pieza Maestra' : 'Nueva Pieza Maestra';
$old    = $old ?? [];
$v      = fn(string $k) => esc($old[$k] ?? ($pieza[$k] ?? ''));
?>
<div class="modal-header" style="border-bottom:1px solid rgba(17,24,39,.08);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:800;font-size:16px;color:var(--compat-ink);">
        <i class='bx bx-wrench' style="color:var(--compat-accent);margin-right:6px;"></i>
        <?= $titulo ?>
    </h5>
    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<form id="pieza-form" hx-post="<?= $action ?>" hx-target="#modal-content" hx-swap="innerHTML">
    <?= csrf_field() ?>

    <div class="modal-body" style="padding:20px;">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
            <?php foreach ($errors as $e): ?>
            <div><i class='bx bx-error-circle'></i> <?= esc($e) ?></div>
            <?php endforeach ?>
        </div>
        <?php endif ?>

        <div class="form-group mb-0">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">
                Nombre <span style="color:#dc2626">*</span>
            </label>
            <input
                type="text"
                name="nombre"
                class="form-control <?= !empty($errors['nombre']) ? 'is-invalid' : '' ?>"
                placeholder="Ej: Filtro de aceite, Buji, Cadena de transmisión…"
                value="<?= $v('nombre') ?>"
                style="font-size:13.5px;"
                autofocus
            >
            <?php if (!empty($errors['nombre'])): ?>
            <div class="invalid-feedback"><?= esc($errors['nombre']) ?></div>
            <?php endif ?>
            <small style="color:#9ca3af;font-size:11.5px;margin-top:4px;display:block;">
                Este nombre se usará como referencia maestra en el catálogo.
            </small>
        </div>
    </div>

    <div class="modal-footer" style="border-top:1px solid rgba(17,24,39,.08);padding:14px 20px;gap:8px;">
        <button type="button" class="btn btn-light" data-dismiss="modal" style="font-size:13px;">Cancelar</button>
        <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--compat-accent),var(--compat-accent-dark));color:#fff;font-size:13px;font-weight:700;border:0;border-radius:8px;padding:8px 20px;">
            <i class='bx bx-save'></i> <?= $isEdit ? 'Guardar cambios' : 'Crear pieza' ?>
        </button>
    </div>
</form>
