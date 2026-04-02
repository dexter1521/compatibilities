<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<!-- KPI Row -->
<div class="row">

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-body text-center">
                <i class='bx bx-street-view' style="font-size:32px;color:#5a5af0;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;"><?= $kpis['motos'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Motocicletas</p>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-body text-center">
                <i class='bx bx-chip' style="font-size:32px;color:#5a5af0;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;"><?= $kpis['piezas'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Piezas</p>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30" style="border-top:3px solid #5a5af0;">
            <div class="card-body text-center">
                <i class='bx bx-link' style="font-size:32px;color:#5a5af0;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;color:#5a5af0;"><?= $kpis['compatibilidades'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Compatibilidades</p>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30" style="border-top:3px solid #28c76f;">
            <div class="card-body text-center">
                <i class='bx bx-check-shield' style="font-size:32px;color:#28c76f;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;color:#28c76f;"><?= $kpis['confirmadas'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Confirmadas</p>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30">
            <div class="card-body text-center">
                <i class='bx bx-barcode' style="font-size:32px;color:#5a5af0;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;"><?= $kpis['productos'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Productos</p>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="card mb-30" style="border-top:3px solid #ea5455;">
            <div class="card-body text-center">
                <i class='bx bx-error' style="font-size:32px;color:#ea5455;"></i>
                <h3 class="mt-2 mb-0" style="font-size:36px;font-weight:700;color:#ea5455;"><?= $kpis['busquedas_miss'] ?? 0 ?></h3>
                <p class="text-muted mb-0" style="font-size:12px;">Sin resultado</p>
            </div>
        </div>
    </div>

</div>

<!-- Segunda fila -->
<div class="row">

    <!-- Columna izquierda -->
    <div class="col-lg-8">

        <!-- Buscador rápido -->
        <div class="card mb-30" x-data="{ q: '' }">
            <div class="card-header">
                <h3><i class='bx bx-search-alt'></i> Búsqueda Rápida</h3>
            </div>
            <div class="card-body">
                <form class="form-inline" hx-get="<?= site_url('/search') ?>" hx-target="#searchPreview" hx-swap="innerHTML">
                    <div class="input-group w-100">
                        <input
                            name="q"
                            type="text"
                            class="form-control"
                            placeholder="balata ft150  ·  filtro cgl  ·  bujia fz150"
                            x-model="q"
                            autocomplete="off"
                        >
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" :disabled="q.trim().length < 2">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
                <div id="searchPreview" class="mt-3"></div>
            </div>
        </div>

        <!-- Últimas importaciones -->
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class='bx bx-upload'></i> Últimas Importaciones</h3>
                <a href="<?= site_url('/import') ?>" class="btn btn-sm btn-outline-primary">Ver todo</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimosImports)): ?>
                <p class="text-muted text-center py-4 mb-0">Sin importaciones — <a href="<?= site_url('/import') ?>">subir Excel</a></p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th class="text-right">Items</th>
                                <th>Estado</th>
                                <th class="text-right">Errores</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimosImports as $job):
                            $badge = match($job['estado']) {
                                'finalizado' => 'success',
                                'error'      => 'danger',
                                'procesando' => 'warning',
                                default      => 'secondary',
                            };
                        ?>
                            <tr>
                                <td><?= esc($job['archivo_nombre']) ?></td>
                                <td class="text-right"><?= (int)$job['procesados'] ?>/<?= (int)$job['total_items'] ?></td>
                                <td><span class="badge badge-<?= $badge ?>"><?= esc(ucfirst($job['estado'])) ?></span></td>
                                <td class="text-right text-danger"><?= (int)$job['errores'] ?: '—' ?></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <?php endif ?>
            </div>
        </div>

    </div>

    <!-- Columna derecha -->
    <div class="col-lg-4">

        <!-- Top búsquedas sin resultado -->
        <div class="card mb-30">
            <div class="card-header">
                <h3><i class='bx bx-bell' style="color:#ea5455;"></i> Piezas sin Catálogo</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topBusquedas)): ?>
                <p class="text-muted text-center py-4 mb-0">Sin registros</p>
                <?php else:
                    $maxC = max(array_column($topBusquedas, 'contador')) ?: 1;
                ?>
                <ul class="list-group list-group-flush">
                <?php foreach ($topBusquedas as $b): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="font-size:13px;"><?= esc($b['termino']) ?></span>
                        <span class="badge badge-danger badge-pill"><?= (int)$b['contador'] ?></span>
                    </li>
                <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="card mb-30">
            <div class="card-header">
                <h3><i class='bx bx-grid-alt'></i> Accesos Rápidos</h3>
            </div>
            <div class="card-body">
                <a href="<?= site_url('/buscador') ?>" class="btn btn-primary btn-block mb-2">
                    <i class='bx bx-search'></i> Abrir Buscador
                </a>
                <a href="<?= site_url('/import') ?>" class="btn btn-outline-primary btn-block mb-2">
                    <i class='bx bx-upload'></i> Importar Excel
                </a>
                <a href="<?= site_url('/motos/create') ?>" class="btn btn-outline-secondary btn-block mb-2">
                    <i class='bx bx-plus'></i> Nueva Moto
                </a>
                <a href="<?= site_url('/piezas/create') ?>" class="btn btn-outline-secondary btn-block mb-2">
                    <i class='bx bx-plus'></i> Nueva Pieza
                </a>
                <a href="<?= site_url('/compatibilidades/create') ?>" class="btn btn-outline-secondary btn-block">
                    <i class='bx bx-plus'></i> Nueva Compatibilidad
                </a>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>