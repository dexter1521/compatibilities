<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<!-- â”€â”€ KPI Row â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
<div class="row" style="margin-bottom:4px;">

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel">
            <span class="kpi-label">Motocicletas</span>
            <span class="kpi-number"><?= $kpis['motos'] ?? 0 ?></span>
            <span class="kpi-sub">registradas</span>
            <i class='bx bx-street-view kpi-icon-bg'></i>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel">
            <span class="kpi-label">Piezas</span>
            <span class="kpi-number"><?= $kpis['piezas'] ?? 0 ?></span>
            <span class="kpi-sub">maestras</span>
            <i class='bx bx-chip kpi-icon-bg'></i>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel kpi-accent">
            <span class="kpi-label">Compatibilidades</span>
            <span class="kpi-number num-accent"><?= $kpis['compatibilidades'] ?? 0 ?></span>
            <span class="kpi-sub">relaciones</span>
            <i class='bx bx-link kpi-icon-bg'></i>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel kpi-confirm">
            <span class="kpi-label">Confirmadas</span>
            <span class="kpi-number num-confirm"><?= $kpis['confirmadas'] ?? 0 ?></span>
            <span class="kpi-sub">verificadas</span>
            <i class='bx bx-check-shield kpi-icon-bg'></i>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel">
            <span class="kpi-label">Productos</span>
            <span class="kpi-number"><?= $kpis['productos'] ?? 0 ?></span>
            <span class="kpi-sub">con clave</span>
            <i class='bx bx-barcode kpi-icon-bg'></i>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="kpi-panel kpi-alert">
            <span class="kpi-label">Sin resultado</span>
            <span class="kpi-number num-alert"><?= $kpis['busquedas_miss'] ?? 0 ?></span>
            <span class="kpi-sub">bÃºsquedas</span>
            <i class='bx bx-error kpi-icon-bg'></i>
        </div>
    </div>

</div>

<!-- â”€â”€ Segunda fila â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
<div class="row">

    <!-- Columna izquierda: buscador + imports -->
    <div class="col-lg-8 mb-3">

        <!-- Buscador rÃ¡pido -->
        <div class="dk-search-panel mb-3" x-data="{ q: '' }">
            <span class="dk-search-eyebrow">// bÃºsqueda rÃ¡pida</span>
            <form class="d-flex gap-2" style="gap:8px;display:flex;" hx-get="<?= site_url('/search') ?>" hx-target="#searchPreview" hx-swap="innerHTML">
                <input
                    name="q"
                    type="text"
                    class="form-control flex-grow-1"
                    placeholder="balata ft150   Â·   filtro cgl   Â·   bujia fz150"
                    x-model="q"
                    autocomplete="off"
                    style="flex:1;"
                >
                <button class="btn btn-primary px-4" type="submit" :disabled="q.trim().length < 2"
                    style="white-space:nowrap;flex-shrink:0;">
                    Buscar
                </button>
            </form>
            <div id="searchPreview" class="mt-3" style="font-family:var(--font-mono);font-size:12px;color:var(--dk-text-3);">
                _
            </div>
        </div>

        <!-- Ãšltimas importaciones -->
        <div style="background:var(--dk-surface);border:1px solid var(--dk-border);border-radius:8px;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--dk-border);">
                <span style="font-family:var(--font-display);font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:1.5px;color:var(--dk-text);">
                    <i class='bx bx-upload' style="color:var(--dk-accent);margin-right:6px;"></i>Importaciones
                </span>
                <a href="<?= site_url('/import') ?>" style="font-family:var(--font-mono);font-size:10px;letter-spacing:1px;color:var(--dk-accent);text-decoration:none;">VER TODO â†’</a>
            </div>

            <?php if (empty($ultimosImports)): ?>
            <div style="padding:20px 16px;font-family:var(--font-mono);font-size:12px;color:var(--dk-text-3);text-align:center;">
                // sin importaciones â€” <a href="<?= site_url('/import') ?>">subir Excel</a>
            </div>
            <?php else: ?>
            <!-- header row -->
            <div class="import-log-row" style="border-bottom:1px solid var(--dk-border);">
                <span style="font-family:var(--font-mono);font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--dk-text-3);">Archivo</span>
                <span style="font-family:var(--font-mono);font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--dk-text-3);text-align:right;">Items</span>
                <span style="font-family:var(--font-mono);font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--dk-text-3);">Estado</span>
                <span style="font-family:var(--font-mono);font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--dk-text-3);text-align:right;">Err</span>
            </div>
            <?php foreach ($ultimosImports as $job):
                $stClass = match($job['estado']) {
                    'finalizado' => 'status-finalizado',
                    'error'      => 'status-error',
                    'procesando' => 'status-procesando',
                    default      => 'status-pendiente',
                };
            ?>
            <div class="import-log-row">
                <span class="import-log-file"><?= esc($job['archivo_nombre']) ?></span>
                <span class="import-log-count"><?= (int)$job['procesados'] ?>/<?= (int)$job['total_items'] ?></span>
                <span><span class="status-badge <?= $stClass ?>"><?= esc(ucfirst($job['estado'])) ?></span></span>
                <span class="import-log-err"><?= (int)$job['errores'] ?: 'â€”' ?></span>
            </div>
            <?php endforeach ?>
            <?php endif ?>
        </div>

    </div>

    <!-- Columna derecha: bÃºsquedas fallidas + accesos rÃ¡pidos -->
    <div class="col-lg-4 mb-3">

        <!-- Top bÃºsquedas sin resultado -->
        <div style="background:var(--dk-surface);border:1px solid rgba(239,68,68,.2);border-radius:8px;overflow:hidden;margin-bottom:12px;">
            <div class="misses-header">
                <i class='bx bx-bell' style="color:var(--dk-danger);font-size:14px;"></i>
                <span class="misses-header-label">Piezas sin catÃ¡logo</span>
            </div>
            <?php if (empty($topBusquedas)): ?>
            <div style="padding:16px;font-family:var(--font-mono);font-size:11px;color:var(--dk-text-3);text-align:center;">// sin registros</div>
            <?php else:
                $maxC = max(array_column($topBusquedas, 'contador')) ?: 1;
            ?>
            <?php foreach ($topBusquedas as $b): ?>
            <div class="miss-item">
                <span class="miss-term" title="<?= esc($b['termino']) ?>"><?= esc($b['termino']) ?></span>
                <div class="miss-bar-wrap">
                    <div class="miss-bar" style="width:<?= round((int)$b['contador'] / $maxC * 100) ?>%;"></div>
                </div>
                <span class="miss-count"><?= (int)$b['contador'] ?></span>
            </div>
            <?php endforeach ?>
            <?php endif ?>
        </div>

        <!-- Accesos rÃ¡pidos -->
        <div style="background:var(--dk-surface);border:1px solid var(--dk-border);border-radius:8px;padding:14px;">
            <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:var(--dk-text-3);margin-bottom:12px;">// accesos rÃ¡pidos</div>
            <a href="<?= site_url('/buscador') ?>" class="dk-action-btn dk-action-primary">
                <i class='bx bx-search'></i> Abrir Buscador
            </a>
            <a href="<?= site_url('/import') ?>" class="dk-action-btn">
                <i class='bx bx-upload'></i> Importar Excel
            </a>
            <a href="<?= site_url('/motos/create') ?>" class="dk-action-btn">
                <i class='bx bx-plus'></i> Nueva Moto
            </a>
            <a href="<?= site_url('/piezas/create') ?>" class="dk-action-btn">
                <i class='bx bx-plus'></i> Nueva Pieza
            </a>
            <a href="<?= site_url('/compatibilidades/create') ?>" class="dk-action-btn">
                <i class='bx bx-plus'></i> Nueva Compatibilidad
            </a>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
