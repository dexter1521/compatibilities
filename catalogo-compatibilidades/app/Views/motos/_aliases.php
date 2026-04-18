<?php
$motoLabel = $moto ? esc($moto['marca_nombre']) . ' ' . esc($moto['modelo']) : 'Moto';
$motoId    = $moto['id'] ?? 0;
$old       = $old ?? [];
?>
<div class="modal-header" style="border-bottom:1px solid rgba(17,24,39,.08);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:800;font-size:16px;color:var(--compat-ink);">
        <i class='bx bx-purchase-tag' style="color:var(--compat-accent);margin-right:6px;"></i>
        Aliases — <span style="color:var(--compat-accent-dark)"><?= $motoLabel ?></span>
    </h5>
    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<div id="aliases-body" class="modal-body" style="padding:20px;min-height:120px;">

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
            <?php foreach ($errors as $e): ?><div><i class='bx bx-error-circle'></i> <?= esc($e) ?></div><?php endforeach ?>
        </div>
    <?php endif ?>

    <?php if (empty($aliases)): ?>
        <p style="color:#9ca3af;font-size:13.5px;text-align:center;padding:16px 0;">
            <i class='bx bx-purchase-tag' style="font-size:28px;display:block;margin-bottom:8px;"></i>
            Sin aliases todavía. Agrega el primero abajo.
        </p>
    <?php else: ?>
        <ul id="alias-list" style="list-style:none;padding:0;margin:0 0 16px;">
            <?php foreach ($aliases as $a): ?>
            <li style="display:flex;align-items:center;justify-content:space-between;
                        padding:8px 12px;border-radius:8px;margin-bottom:6px;
                        background:rgba(249,115,22,.05);border:1px solid rgba(249,115,22,.12);">
                <span style="font-size:13.5px;font-weight:600;color:var(--compat-ink);">
                    <i class='bx bx-purchase-tag' style="color:var(--compat-accent);margin-right:4px;font-size:13px;"></i>
                    <?= esc($a['alias']) ?>
                </span>
                <button
                    type="button"
                    title="Eliminar"
                    hx-post="<?= site_url('/motos/alias/' . $a['id'] . '/delete') ?>"
                    hx-target="#modal-content"
                    hx-swap="innerHTML"
                    hx-confirm="¿Eliminar el alias '<?= esc($a['alias']) ?>'?"
                    style="background:rgba(220,38,38,.08);color:#dc2626;border:0;
                           border-radius:6px;width:26px;height:26px;display:inline-flex;
                           align-items:center;justify-content:center;cursor:pointer;
                           font-size:14px;flex-shrink:0;transition:background .15s;"
                    onmouseover="this.style.background='rgba(220,38,38,.18)'"
                    onmouseout="this.style.background='rgba(220,38,38,.08)'">
                    <i class='bx bx-trash'></i>
                </button>
            </li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

    <form id="alias-form"
          hx-post="<?= site_url('/motos/' . $motoId . '/aliases/store') ?>"
          hx-target="#modal-content"
          hx-swap="innerHTML">
        <?= csrf_field() ?>
        <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;">
            Nuevo alias
        </label>
        <div style="display:flex;gap:8px;">
            <input
                type="text"
                name="alias"
                class="form-control"
                placeholder="Ej: Honda CBR600 F4i, CBR 600, CBR-600…"
                value="<?= esc($old['alias'] ?? '') ?>"
                style="font-size:13.5px;flex:1;"
                autofocus>
            <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--compat-accent),var(--compat-accent-dark));color:#fff;font-size:13px;font-weight:700;border:0;border-radius:8px;padding:8px 16px;white-space:nowrap;flex-shrink:0;">
                <i class='bx bx-plus'></i> Agregar
            </button>
        </div>
        <small style="color:#9ca3af;font-size:11.5px;margin-top:5px;display:block;">
            El alias es el texto que el importador buscará en la descripción del producto para vincular la moto.
        </small>
    </form>

</div>

<div class="modal-footer" style="border-top:1px solid rgba(17,24,39,.08);padding:12px 20px;">
    <button type="button" class="btn btn-light" data-dismiss="modal" style="font-size:13px;">Cerrar</button>
    <span style="font-size:12px;color:#9ca3af;margin-left:4px;"><?= count($aliases) ?> alias<?= count($aliases) !== 1 ? 'es' : '' ?> registrado<?= count($aliases) !== 1 ? 's' : '' ?></span>
</div>
