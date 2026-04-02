<?php
$total    = count($pendientes);
$sinMoto  = count(array_filter($pendientes, fn($r) => $r['enrich_estado'] === 'sin_moto'));
$sinTipo  = count(array_filter($pendientes, fn($r) => $r['enrich_estado'] === 'sin_tipo'));
$sinAmbos = count(array_filter($pendientes, fn($r) => $r['enrich_estado'] === 'sin_ambos'));
$sinProcesar = count(array_filter($pendientes, fn($r) => $r['enrich_estado'] === null));

$labelMap = [
    'sin_moto'   => ['txt' => 'Sin moto',   'cls' => 'badge-warning'],
    'sin_tipo'   => ['txt' => 'Sin tipo',   'cls' => 'badge-info'],
    'sin_ambos'  => ['txt' => 'Sin ambos',  'cls' => 'badge-danger'],
    null         => ['txt' => 'Sin procesar','cls' => 'badge-secondary'],
];
?>

<?php if ($total === 0): ?>
<div style="text-align:center;padding:40px;color:#9ca3af;font-size:14px;">
    <i class='bx bx-check-circle' style="font-size:42px;color:#15803d;display:block;margin-bottom:10px;"></i>
    <strong style="color:#15803d;font-size:16px;">¡Sin pendientes!</strong><br>
    Todos los productos están enriquecidos correctamente.
</div>
<?php else: ?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:10px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <span class="badge badge-secondary" style="font-size:12px;"><?= $total ?> total</span>
        <?php if ($sinMoto):  ?><span class="badge badge-warning"   style="font-size:12px;"><?= $sinMoto ?> sin moto</span><?php endif ?>
        <?php if ($sinTipo):  ?><span class="badge badge-info"      style="font-size:12px;"><?= $sinTipo ?> sin tipo</span><?php endif ?>
        <?php if ($sinAmbos): ?><span class="badge badge-danger"    style="font-size:12px;"><?= $sinAmbos ?> sin ambos</span><?php endif ?>
        <?php if ($sinProcesar): ?><span class="badge badge-secondary" style="font-size:12px;"><?= $sinProcesar ?> sin procesar</span><?php endif ?>
    </div>
    <form
        method="POST"
        action="<?= site_url('/import/reenrich') ?>"
        hx-post="<?= site_url('/import/reenrich') ?>"
        hx-target="#pendientes-container"
        hx-swap="innerHTML"
        hx-indicator="#reenrich-indicator"
    >
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center" style="gap:5px;font-weight:700;">
            <i class='bx bx-refresh' id="reenrich-indicator" style="font-size:15px;"></i>
            Reintentar <?= $total ?> pendientes
        </button>
    </form>
</div>

<div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
        <thead>
            <tr style="border-bottom:1px solid rgba(17,24,39,.09);">
                <th style="padding:8px 10px;text-align:left;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Motivo</th>
                <th style="padding:8px 10px;text-align:left;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Clave</th>
                <th style="padding:8px 10px;text-align:left;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Descripción</th>
                <th style="padding:8px 10px;text-align:left;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Proveedor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendientes as $p): ?>
            <?php $lbl = $labelMap[$p['enrich_estado']] ?? $labelMap[null]; ?>
            <tr style="border-bottom:1px solid rgba(17,24,39,.05);">
                <td style="padding:8px 10px;white-space:nowrap;">
                    <span class="badge <?= $lbl['cls'] ?>" style="font-size:11px;"><?= $lbl['txt'] ?></span>
                </td>
                <td style="padding:8px 10px;font-family:monospace;font-size:12px;color:#374151;">
                    <?= esc($p['clave_proveedor']) ?>
                </td>
                <td style="padding:8px 10px;color:#111827;max-width:380px;">
                    <?= esc($p['nombre']) ?>
                </td>
                <td style="padding:8px 10px;color:#6b7280;font-size:12px;">
                    <?= esc($p['proveedor'] ?? '—') ?>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php endif ?>
