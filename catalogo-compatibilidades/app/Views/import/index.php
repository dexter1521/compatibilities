<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<style>
    /* ── Upload zone ──────────────────────────────────────── */
    .import-hero {
        background:
            radial-gradient(ellipse at 5% 0%,  rgba(249,115,22,.15) 0%, transparent 45%),
            radial-gradient(ellipse at 95% 100%, rgba(14,116,144,.12) 0%, transparent 45%),
            #fff;
        border: 1px solid rgba(17,24,39,.07);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 28px;
    }

    .import-label {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(249,115,22,.1);
        color: var(--compat-accent-dark);
        border: 1px solid rgba(249,115,22,.3);
        border-radius: 999px; padding: 5px 12px;
        font-size: 11.5px; font-weight: 700;
        letter-spacing: .5px; text-transform: uppercase;
        margin-bottom: 14px;
    }

    .import-title {
        font-size: 22px; font-weight: 800; color: var(--compat-ink);
        margin-bottom: 4px; letter-spacing: -.3px;
    }
    .import-sub {
        font-size: 13.5px; color: #6b7280; margin-bottom: 22px;
    }

    .dropzone {
        border: 2px dashed rgba(249,115,22,.4);
        border-radius: 12px;
        padding: 36px 24px;
        text-align: center;
        background: rgba(249,115,22,.03);
        cursor: pointer;
        transition: background .2s, border-color .2s;
        position: relative;
    }
    .dropzone:hover, .dropzone.drag-over {
        background: rgba(249,115,22,.07);
        border-color: var(--compat-accent);
    }
    .dropzone input[type=file] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%;
    }
    .dropzone-icon { font-size: 40px; color: var(--compat-accent); margin-bottom: 10px; display: block; }
    .dropzone-text { font-size: 15px; font-weight: 700; color: var(--compat-ink); margin-bottom: 4px; }
    .dropzone-hint { font-size: 12px; color: #9ca3af; }
    .dropzone-file-name { font-size: 13px; font-weight: 600; color: var(--compat-accent-dark); margin-top: 10px; }

    .btn-upload {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 11px 24px; margin-top: 18px;
        background: linear-gradient(135deg, var(--compat-accent), var(--compat-accent-dark));
        color: #fff; border: 0; border-radius: 10px;
        font-size: 14px; font-weight: 700; cursor: pointer;
        transition: filter .15s;
    }
    .btn-upload:hover { filter: brightness(.96); }
    .btn-upload:disabled { filter: grayscale(1) opacity(.6); cursor: not-allowed; }

    /* ── Instrucciones columnas ───────────────────────────── */
    .col-format-card {
        background: rgba(14,116,144,.05);
        border: 1px solid rgba(14,116,144,.15);
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 16px;
        font-size: 12.5px;
        color: #374151;
    }
    .col-format-card strong { color: #0e7490; }
    .col-format-card code {
        background: rgba(14,116,144,.1);
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 11.5px;
        color: #0e7490;
    }

    /* ── Jobs table ──────────────────────────────────────── */
    .jobs-card { background: #fff; border: 1px solid rgba(17,24,39,.08); border-radius: 14px; overflow: hidden; margin-bottom: 16px; }
    .jobs-card-header { padding: 14px 20px; border-bottom: 1px solid rgba(17,24,39,.07); display: flex; align-items: center; justify-content: space-between; }
    .jobs-card-title { font-size: 14px; font-weight: 800; color: var(--compat-ink); }

    .jobs-table { width: 100%; border-collapse: collapse; }
    .jobs-table thead th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #6b7280; padding: 10px 16px; border-bottom: 1px solid rgba(17,24,39,.09); background: rgba(249,115,22,.02); }
    .jobs-table tbody td { padding: 11px 16px; font-size: 13px; color: var(--compat-ink); border-bottom: 1px solid rgba(17,24,39,.05); vertical-align: middle; }
    .jobs-table tbody tr:last-child td { border-bottom: 0; }
    .jobs-table tbody tr:hover { background: rgba(249,115,22,.02); }

    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        border-radius: 6px; padding: 3px 9px;
        font-size: 11px; font-weight: 700;
    }
    .status-finalizado { background: rgba(34,197,94,.1); color: #15803d; border: 1px solid rgba(34,197,94,.25); }
    .status-error      { background: rgba(220,38,38,.08); color: #dc2626; border: 1px solid rgba(220,38,38,.2); }
    .status-procesando { background: rgba(234,179,8,.1); color: #854d0e; border: 1px solid rgba(234,179,8,.25); }
    .status-pendiente  { background: rgba(156,163,175,.1); color: #6b7280; border: 1px solid rgba(156,163,175,.25); }

    .btn-detail {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; font-size: 12px; font-weight: 600;
        background: rgba(14,116,144,.08); color: #0e7490;
        border: 1px solid rgba(14,116,144,.2); border-radius: 7px; cursor: pointer;
        transition: background .15s;
    }
    .btn-detail:hover { background: rgba(14,116,144,.16); }

    /* ── Upload progress overlay ─────────────────────────── */
    .upload-progress {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(17,24,39,.5);
        align-items: center; justify-content: center;
    }
    .upload-progress.active { display: flex; }
    .upload-progress-inner {
        background: #fff; border-radius: 16px; padding: 36px 48px;
        text-align: center; box-shadow: 0 24px 60px rgba(17,24,39,.2);
        min-width: 280px;
    }
    .upload-progress-inner i { font-size: 42px; color: var(--compat-accent); margin-bottom: 12px; display: block; animation: spin 1.2s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .upload-progress-text { font-size: 15px; font-weight: 700; color: var(--compat-ink); margin-bottom: 4px; }
    .upload-progress-sub { font-size: 12.5px; color: #6b7280; }

    /* ── Detail modal ──────────────────────────────────────── */
    .detail-items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .detail-items-table th { padding: 7px 10px; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; color: #9ca3af; border-bottom: 1px solid rgba(17,24,39,.08); }
    .detail-items-table td { padding: 7px 10px; border-bottom: 1px solid rgba(17,24,39,.05); color: var(--compat-ink); }
    .detail-items-table tr:last-child td { border-bottom: 0; }
</style>

<!-- Upload progress overlay -->
<div class="upload-progress" id="upload-overlay">
    <div class="upload-progress-inner">
        <i class='bx bx-loader-alt'></i>
        <p class="upload-progress-text">Procesando archivo…</p>
        <p class="upload-progress-sub">Esto puede tardar unos segundos.</p>
    </div>
</div>

<!-- ── Hero + formulario de carga ──────────────────────────── -->
<div class="import-hero">
    <span class="import-label"><i class='bx bx-upload'></i> Importador</span>
    <h1 class="import-title">Importar productos desde Excel</h1>
    <p class="import-sub">Sube tu archivo de MyBusiness y el catálogo se actualiza automáticamente.</p>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
        <i class='bx bx-error-circle'></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px;">
        <i class='bx bx-check-circle'></i> <?= esc(session()->getFlashdata('success')) ?>
    </div>
    <?php endif ?>

    <form
        method="POST"
        action="<?= site_url('/import/upload') ?>"
        enctype="multipart/form-data"
        id="import-form"
        x-data="{ fileName: '' }"
        @submit="document.getElementById('upload-overlay').classList.add('active')"
    >
        <?= csrf_field() ?>

        <div
            class="dropzone"
            id="dropzone"
            @dragover.prevent="$el.classList.add('drag-over')"
            @dragleave="$el.classList.remove('drag-over')"
            @drop.prevent="
                $el.classList.remove('drag-over');
                const f = $event.dataTransfer.files[0];
                if (f) { $refs.fileInput.files = $event.dataTransfer.files; fileName = f.name; }
            "
        >
            <input
                type="file"
                name="archivo"
                accept=".xlsx,.xls,.csv"
                x-ref="fileInput"
                @change="fileName = $event.target.files[0]?.name || ''"
            >
            <i class='bx bx-spreadsheet dropzone-icon'></i>
            <p class="dropzone-text">Arrastra tu archivo aquí o haz clic</p>
            <p class="dropzone-hint">.xlsx · .xls · .csv &nbsp;—&nbsp; máx. 20 MB</p>
            <p class="dropzone-file-name" x-text="fileName" x-show="fileName"></p>
        </div>

        <div class="col-format-card">
            <strong>Columnas esperadas</strong> (el orden no importa si los encabezados están presentes):<br>
            <code>proveedor</code> &nbsp;·&nbsp;
            <code>clave_proveedor</code> &nbsp;·&nbsp;
            <code>nombre</code> (o <code>descripcion</code>)<br>
            <span style="color:#9ca3af;">Sin encabezados: columna A = proveedor, B = clave, C = nombre.</span>
        </div>

        <button type="submit" class="btn-upload" :disabled="!fileName">
            <i class='bx bx-cloud-upload'></i> Importar ahora
        </button>
    </form>
</div>

<!-- ── Pendientes de enriquecimiento ────────────────────────── -->
<div class="jobs-card">
    <div class="jobs-card-header">
        <span class="jobs-card-title">
            <i class='bx bx-error-circle' style="color:#f97316;margin-right:6px;"></i>
            Productos pendientes de enriquecimiento
        </span>
        <div style="display:flex;gap:8px;align-items:center;">
            <button
                class="btn-detail"
                hx-post="<?= site_url('/import/detectar-modelos') ?>"
                hx-target="body"
                hx-swap="none"
                hx-confirm="¿Detectar modelos y generar aliases automáticamente?"
                style="background:rgba(249,115,22,.09);color:var(--compat-accent);border-color:rgba(249,115,22,.3);"
            ><i class='bx bx-purchase-tag'></i> Detectar modelos</button>
            <button
                class="btn-detail"
                hx-post="<?= site_url('/import/reenrich') ?>"
                hx-target="body"
                hx-swap="none"
            ><i class='bx bx-recycle'></i> Re-enriquecer</button>
            <button
                class="btn-detail"
                hx-get="<?= site_url('/import/pendientes') ?>"
                hx-target="#pendientes-container"
                hx-swap="innerHTML"
                hx-trigger="load, click"
            ><i class='bx bx-refresh'></i> Actualizar</button>
        </div>
    </div>
    <div id="pendientes-container" style="padding:16px 20px;">
        <div style="text-align:center;color:#9ca3af;font-size:13px;padding:20px 0;">
            <i class='bx bx-loader-alt' style="animation:spin 1s linear infinite;"></i> Cargando…
        </div>
    </div>
</div>

<!-- ── Historial de importaciones ──────────────────────────── -->
<div class="jobs-card">
    <div class="jobs-card-header">
        <span class="jobs-card-title"><i class='bx bx-history' style="color:var(--compat-accent);margin-right:6px;"></i>Historial de importaciones</span>
        <span style="font-size:12px;color:#6b7280;"><?= count($jobs) ?> recientes</span>
    </div>

    <?php if (empty($jobs)): ?>
    <div style="text-align:center;padding:40px;color:#9ca3af;font-size:14px;">
        <i class='bx bx-inbox' style="font-size:36px;display:block;margin-bottom:10px;"></i>
        Aún no hay importaciones registradas.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="jobs-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Archivo</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>OK</th>
                    <th>Errores</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $j): ?>
                <tr>
                    <td style="color:#d1d5db;font-size:11px;"><?= (int) $j['id'] ?></td>
                    <td style="font-weight:600;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <i class='bx bx-file' style="color:#9ca3af;margin-right:4px;"></i>
                        <?= esc($j['archivo_nombre']) ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?= esc($j['estado']) ?>">
                            <?php $icons = ['finalizado'=>'bx-check-circle','error'=>'bx-error-circle','procesando'=>'bx-loader-alt','pendiente'=>'bx-time']; ?>
                            <i class='bx <?= $icons[$j['estado']] ?? 'bx-circle' ?>'></i>
                            <?= esc(ucfirst($j['estado'])) ?>
                        </span>
                    </td>
                    <td><?= (int) $j['total_items'] ?></td>
                    <td style="color:#15803d;font-weight:700;"><?= (int) $j['procesados'] ?></td>
                    <td style="color:<?= (int)$j['errores'] > 0 ? '#dc2626' : '#d1d5db' ?>;font-weight:700;"><?= (int) $j['errores'] ?></td>
                    <td style="font-size:12px;color:#6b7280;">
                        <?= $j['created_at'] ? date('d/m/Y H:i', strtotime($j['created_at'])) : '—' ?>
                    </td>
                    <td>
                        <button
                            class="btn-detail"
                            hx-get="<?= site_url('/import/job/' . $j['id']) ?>"
                            hx-target="#modal-content"
                            hx-swap="innerHTML"
                        ><i class='bx bx-list-ul'></i> Ver</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
