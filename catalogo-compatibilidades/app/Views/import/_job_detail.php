<?php
$estado = $job['estado'];
$badgeClass = [
    'finalizado' => 'status-finalizado',
    'error'      => 'status-error',
    'procesando' => 'status-procesando',
    'pendiente'  => 'status-pendiente',
][$estado] ?? 'status-pendiente';
?>
<div class="modal-header" style="border-bottom:1px solid rgba(17,24,39,.08);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:800;font-size:15px;color:var(--compat-ink);">
        <i class='bx bx-file' style="color:var(--compat-accent);margin-right:6px;"></i>
        Job #<?= (int) $job['id'] ?> — <?= esc($job['archivo_nombre']) ?>
    </h5>
    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>

<div class="modal-body" style="padding:20px;">

    <!-- Resumen -->
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:18px;">
        <div style="flex:1;min-width:120px;background:rgba(249,115,22,.05);border:1px solid rgba(249,115,22,.15);border-radius:10px;padding:12px 16px;text-align:center;">
            <div style="font-size:24px;font-weight:800;color:var(--compat-ink);"><?= (int) $job['total_items'] ?></div>
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Total</div>
        </div>
        <div style="flex:1;min-width:120px;background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:12px 16px;text-align:center;">
            <div style="font-size:24px;font-weight:800;color:#15803d;"><?= (int) $job['procesados'] ?></div>
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Procesados</div>
        </div>
        <div style="flex:1;min-width:120px;background:rgba(220,38,38,.05);border:1px solid rgba(220,38,38,.15);border-radius:10px;padding:12px 16px;text-align:center;">
            <div style="font-size:24px;font-weight:800;color:#dc2626;"><?= (int) $job['errores'] ?></div>
            <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Errores</div>
        </div>
        <div style="flex:1;min-width:120px;background:#f9fafb;border:1px solid rgba(17,24,39,.08);border-radius:10px;padding:12px 16px;text-align:center;">
            <div style="font-size:13px;font-weight:700;">
                <span class="status-badge <?= $badgeClass ?>"><?= esc(ucfirst($estado)) ?></span>
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Estado</div>
        </div>
    </div>

    <!-- Tabla de ítems -->
    <?php if (empty($items)): ?>
    <p style="text-align:center;color:#9ca3af;padding:24px 0;">Sin ítems registrados.</p>
    <?php else: ?>
    <div style="max-height:360px;overflow-y:auto;border:1px solid rgba(17,24,39,.07);border-radius:10px;">
        <table class="detail-items-table">
            <thead style="position:sticky;top:0;background:#fff;z-index:1;">
                <tr>
                    <th>Fila</th>
                    <th>Proveedor</th>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="color:#d1d5db;"><?= (int) $item['fila_numero'] ?></td>
                    <td style="color:#6b7280;"><?= esc($item['proveedor'] ?: '—') ?></td>
                    <td style="font-weight:700;font-family:'Courier New',monospace;font-size:12px;">
                        <?= esc($item['clave_proveedor']) ?>
                    </td>
                    <td><?= esc($item['nombre']) ?></td>
                    <td>
                        <?php if ($item['estado'] === 'procesado'): ?>
                        <span style="color:#15803d;font-weight:700;font-size:11px;">✓ OK</span>
                        <?php elseif ($item['estado'] === 'error'): ?>
                        <span style="color:#dc2626;font-weight:700;font-size:11px;">✗ Error</span>
                        <?php else: ?>
                        <span style="color:#9ca3af;font-size:11px;">—</span>
                        <?php endif ?>
                    </td>
                    <td style="font-size:11px;color:#dc2626;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($item['mensaje_error'] ?? '') ?>">
                        <?= esc(mb_substr($item['mensaje_error'] ?? '', 0, 60)) ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php endif ?>
</div>

<div class="modal-footer" style="border-top:1px solid rgba(17,24,39,.08);padding:12px 20px;">
    <button type="button" class="btn btn-light" data-dismiss="modal" style="font-size:13px;">Cerrar</button>
</div>
