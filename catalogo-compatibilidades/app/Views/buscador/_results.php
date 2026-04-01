<style>
    /* ── Result cards ─────────────────────────────────────────── */
    .result-card {
        background: #fff;
        border: 1px solid rgba(17,24,39,.08);
        border-radius: 14px;
        margin-bottom: 16px;
        overflow: hidden;
        transition: box-shadow .2s;
    }

    .result-card:hover {
        box-shadow: 0 4px 20px rgba(17,24,39,.08);
    }

    .result-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px 14px;
        border-bottom: 1px solid rgba(17,24,39,.07);
        background: linear-gradient(90deg, rgba(249,115,22,.05) 0%, transparent 60%);
        gap: 12px;
        flex-wrap: wrap;
    }

    .result-pieza-name {
        font-size: 17px;
        font-weight: 800;
        color: var(--compat-ink);
        margin: 0;
        letter-spacing: -.2px;
    }

    .result-pieza-name i {
        color: var(--compat-accent);
        margin-right: 6px;
        font-size: 18px;
        vertical-align: middle;
    }

    .result-claves {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .clave-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(249,115,22,.08);
        color: var(--compat-accent-dark);
        border: 1px solid rgba(249,115,22,.25);
        border-radius: 6px;
        padding: 3px 9px;
        font-size: 12px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }

    .clave-badge .prov-label {
        font-family: inherit;
        font-weight: 400;
        opacity: .7;
        font-size: 11px;
    }

    /* ── Compat table ─────────────────────────────────────────── */
    .compat-table-wrapper {
        padding: 16px 20px 20px;
    }

    .compat-table-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .compat-table {
        width: 100%;
        border-collapse: collapse;
    }

    .compat-table thead th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7280;
        border-bottom: 1px solid rgba(17,24,39,.09);
        padding: 6px 8px;
    }

    .compat-table tbody tr:hover {
        background: rgba(249,115,22,.04);
    }

    .compat-table td {
        padding: 9px 8px;
        font-size: 13.5px;
        color: var(--compat-ink);
        border-bottom: 1px solid rgba(17,24,39,.05);
        vertical-align: middle;
    }

    .compat-table tr:last-child td {
        border-bottom: 0;
    }

    .moto-name {
        font-weight: 700;
    }

    .moto-meta {
        font-size: 12px;
        color: #9ca3af;
        display: block;
    }

    /* ── Confirm button ───────────────────────────────────────── */
    .compat-confirm-form {
        display: inline-block;
    }

    .btn-confirm {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid rgba(34,197,94,.5);
        background: rgba(34,197,94,.06);
        color: #15803d;
        border-radius: 8px;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        white-space: nowrap;
    }

    .btn-confirm:hover {
        background: rgba(34,197,94,.14);
        border-color: rgba(34,197,94,.7);
    }

    .compat-confirmed-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(34,197,94,.12);
        color: #15803d;
        border-radius: 8px;
        border: 1.5px solid rgba(34,197,94,.35);
    }

    .compat-confirmed-count {
        background: #15803d;
        color: #fff;
        border-radius: 999px;
        padding: 0 6px;
        font-size: 10px;
        line-height: 18px;
    }

    .result-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(17,24,39,.06);
        color: #374151;
        border-radius: 8px;
        padding: 3px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .no-compat-msg {
        padding: 12px 0 4px;
        font-size: 13px;
        color: #9ca3af;
        font-style: italic;
    }
</style>

<?php
$totalPiezas = count($results);
$totalCompats = 0;
foreach ($results as $r) { $totalCompats += count($r['compatibilidades']); }
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <span class="result-count-badge">
            <i class='bx bx-list-check'></i>
            <?= $totalPiezas ?> pieza<?= $totalPiezas !== 1 ? 's' : '' ?> •
            <?= $totalCompats ?> compatibilidad<?= $totalCompats !== 1 ? 'es' : '' ?>
        </span>
        <span style="font-size:13px; color:#6b7280; margin-left:10px;">
            para <strong>"<?= esc($q) ?>"</strong>
        </span>
    </div>
</div>

<?php foreach ($results as $pieza): ?>
<div class="result-card">

    <!-- Header: nombre de pieza + claves de proveedor -->
    <div class="result-card-header">
        <h3 class="result-pieza-name">
            <i class='bx bx-wrench'></i><?= esc($pieza['pieza_nombre']) ?>
        </h3>

        <?php if (!empty($pieza['productos'])): ?>
        <div class="result-claves">
            <?php foreach ($pieza['productos'] as $prod): ?>
            <span class="clave-badge">
                <?= esc($prod['clave_proveedor']) ?>
                <?php if ($prod['proveedor']): ?>
                <span class="prov-label">(<?= esc($prod['proveedor']) ?>)</span>
                <?php endif ?>
            </span>
            <?php endforeach ?>
        </div>
        <?php endif ?>
    </div>

    <!-- Body: tabla de compatibilidades -->
    <div class="compat-table-wrapper">

        <?php if (empty($pieza['compatibilidades'])): ?>
            <p class="no-compat-msg">
                <i class='bx bx-info-circle'></i>
                Sin compatibilidades registradas aún.
            </p>
        <?php else: ?>
            <p class="compat-table-title">
                <i class='bx bx-car'></i> Compatible con
            </p>
            <table class="compat-table">
                <thead>
                    <tr>
                        <th>Motocicleta</th>
                        <th>Años</th>
                        <th>CC</th>
                        <th style="text-align:right;">¿Funcionó?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pieza['compatibilidades'] as $compat): ?>
                    <tr>
                        <td>
                            <span class="moto-name">
                                <?= esc(($compat['marca_nombre'] ?? '') . ' ' . ($compat['moto_modelo'] ?? '')) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($compat['anio_desde'] || $compat['anio_hasta']): ?>
                                <?= esc($compat['anio_desde'] ?? '?') ?>
                                <?= ($compat['anio_hasta'] && $compat['anio_hasta'] != $compat['anio_desde'])
                                    ? '– ' . esc($compat['anio_hasta'])
                                    : '' ?>
                            <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <?= $compat['cilindrada'] ? esc($compat['cilindrada']) : '<span style="color:#d1d5db">—</span>' ?>
                        </td>
                        <td style="text-align:right;">
                            <?php if ($compat['confirmada']): ?>
                            <span class="compat-confirmed-badge" id="confirm-btn-<?= $compat['id'] ?>">
                                <i class='bx bx-check-circle'></i> Confirmado
                                <span class="compat-confirmed-count"><?= (int) $compat['contador_confirmaciones'] ?></span>
                            </span>
                            <?php else: ?>
                            <form
                                class="compat-confirm-form"
                                hx-post="<?= site_url('/compatibilidades/' . $compat['id'] . '/confirm') ?>"
                                hx-target="#confirm-btn-<?= $compat['id'] ?>"
                                hx-swap="outerHTML"
                                id="confirm-btn-<?= $compat['id'] ?>"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-confirm">
                                    <i class='bx bx-check'></i> Funcionó
                                    <?php if ($compat['contador_confirmaciones'] > 0): ?>
                                    <span class="compat-confirmed-count"><?= (int) $compat['contador_confirmaciones'] ?></span>
                                    <?php endif ?>
                                </button>
                            </form>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>

    </div>
</div>
<?php endforeach ?>
