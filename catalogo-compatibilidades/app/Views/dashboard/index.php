<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<!-- KPI Row 1 -->
<div class="row compat-kpi">
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Motos</h3><i class='bx bx-motorcycle'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['motos'] ?? 0)) ?></h2>
                <span class="trend">registradas</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Piezas</h3><i class='bx bx-cog'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['piezas'] ?? 0)) ?></h2>
                <span class="trend">maestras</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Compats.</h3><i class='bx bx-link-alt'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['compatibilidades'] ?? 0)) ?></h2>
                <span class="trend">relaciones</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Confirmadas</h3><i class='bx bx-check-shield'></i>
            </div>
            <div class="card-body">
                <h2 style="color:var(--compat-accent);"><?= esc((string) ($kpis['confirmadas'] ?? 0)) ?></h2>
                <span class="trend">verificadas</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Productos</h3><i class='bx bx-package'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['productos'] ?? 0)) ?></h2>
                <span class="trend">con clave</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Sin resultado</h3><i class='bx bx-search-alt'></i>
            </div>
            <div class="card-body">
                <h2 style="color:#dc2626;"><?= esc((string) ($kpis['busquedas_miss'] ?? 0)) ?></h2>
                <span class="trend">búsquedas</span>
            </div>
        </div>
    </div>
</div>

<!-- Segunda fila: buscador rápido + tablas -->
<div class="row">

    <!-- Buscador rápido -->
    <div class="col-lg-8">
        <div class="card mb-30 compat-quick-search" x-data="{ q: '' }">
            <div class="card-header">
                <h3><i class='bx bx-search' style="color:var(--compat-accent);margin-right:6px;"></i>Buscador rápido</h3>
            </div>
            <div class="card-body">
                <form class="row" hx-get="<?= site_url('/search') ?>" hx-target="#searchPreview" hx-swap="innerHTML">
                    <div class="col-lg-9 col-md-8 mb-2 mb-md-0">
                        <input
                            name="q"
                            type="text"
                            class="form-control"
                            placeholder="Ejemplo: balata ft150, filtro cgl, bujia fz"
                            x-model="q"
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <button class="btn btn-primary btn-block" type="submit" :disabled="q.trim().length < 2">Buscar</button>
                    </div>
                </form>
                <div id="searchPreview" class="mt-3 text-muted" style="font-size:13px;">
                    Escribe al menos 2 caracteres y presiona Buscar.
                </div>
            </div>
        </div>

        <!-- Últimos imports -->
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class='bx bx-upload' style="color:var(--compat-accent);margin-right:6px;"></i>Últimas importaciones</h3>
                <a href="<?= site_url('/import') ?>" class="btn btn-sm" style="background:rgba(249,115,22,.1);color:var(--compat-accent);font-size:12px;font-weight:700;border-radius:8px;padding:4px 12px;">Ver todo</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimosImports)): ?>
                <p class="text-muted text-center py-3" style="font-size:13px;">Sin importaciones aún. <a href="<?= site_url('/import') ?>">Subir Excel</a></p>
                <?php else: ?>
                <table class="table table-sm mb-0" style="font-size:13px;">
                    <thead><tr><th>Archivo</th><th>Estado</th><th>Items</th><th>Errores</th></tr></thead>
                    <tbody>
                        <?php foreach ($ultimosImports as $job): ?>
                        <?php
                            $stClass = match($job['estado']) {
                                'finalizado' => 'status-finalizado',
                                'error'      => 'status-error',
                                'procesando' => 'status-procesando',
                                default      => 'status-pendiente',
                            };
                        ?>
                        <tr>
                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc($job['archivo_nombre']) ?></td>
                            <td><span class="status-badge <?= $stClass ?>"><?= esc(ucfirst($job['estado'])) ?></span></td>
                            <td><?= (int) $job['procesados'] ?>/<?= (int) $job['total_items'] ?></td>
                            <td style="color:<?= (int)$job['errores'] > 0 ? '#dc2626' : '#9ca3af' ?>;"><?= (int) $job['errores'] ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- Top búsquedas sin resultado -->
    <div class="col-lg-4">
        <div class="card mb-30">
            <div class="card-header">
                <h3><i class='bx bx-bell' style="color:#dc2626;margin-right:6px;"></i>Piezas más buscadas sin resultado</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topBusquedas)): ?>
                <p class="text-muted text-center py-3" style="font-size:13px;">Sin registros aún.</p>
                <?php else: ?>
                <?php
                $maxContador = max(array_column($topBusquedas, 'contador')) ?: 1;
                ?>
                <div style="padding:12px 16px;">
                    <?php foreach ($topBusquedas as $b): ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-size:12px;font-weight:600;color:var(--compat-ink);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($b['termino']) ?>"><?= esc($b['termino']) ?></span>
                            <span style="font-size:11px;font-weight:700;color:#dc2626;"><?= (int) $b['contador'] ?>x</span>
                        </div>
                        <div style="height:5px;background:rgba(220,38,38,.1);border-radius:4px;">
                            <div style="height:100%;width:<?= round((int)$b['contador'] / $maxContador * 100) ?>%;background:#dc2626;border-radius:4px;"></div>
                        </div>
                    </div>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="card mb-30">
            <div class="card-header"><h3>Accesos rápidos</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                <a href="<?= site_url('/motos/create') ?>" class="btn btn-block" style="background:rgba(249,115,22,.08);color:var(--compat-accent);font-weight:700;font-size:13px;border:1px solid rgba(249,115,22,.2);border-radius:8px;">
                    <i class='bx bx-plus-circle' style="margin-right:6px;"></i>Nueva motocicleta
                </a>
                <a href="<?= site_url('/piezas/create') ?>" class="btn btn-block" style="background:rgba(249,115,22,.08);color:var(--compat-accent);font-weight:700;font-size:13px;border:1px solid rgba(249,115,22,.2);border-radius:8px;">
                    <i class='bx bx-plus-circle' style="margin-right:6px;"></i>Nueva pieza maestra
                </a>
                <a href="<?= site_url('/compatibilidades/create') ?>" class="btn btn-block" style="background:rgba(249,115,22,.08);color:var(--compat-accent);font-weight:700;font-size:13px;border:1px solid rgba(249,115,22,.2);border-radius:8px;">
                    <i class='bx bx-plus-circle' style="margin-right:6px;"></i>Nueva compatibilidad
                </a>
                <a href="<?= site_url('/import') ?>" class="btn btn-block" style="background:rgba(249,115,22,.08);color:var(--compat-accent);font-weight:700;font-size:13px;border:1px solid rgba(249,115,22,.2);border-radius:8px;">
                    <i class='bx bx-upload' style="margin-right:6px;"></i>Importar Excel
                </a>
                <a href="<?= site_url('/buscador') ?>" class="btn btn-block" style="background:var(--compat-accent);color:#fff;font-weight:700;font-size:13px;border-radius:8px;">
                    <i class='bx bx-search' style="margin-right:6px;"></i>Abrir buscador
                </a>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
