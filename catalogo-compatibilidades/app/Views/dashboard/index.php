<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>
<div class="compat-hero">
    <span class="compat-badge"><i class='bx bx-rocket'></i> Integracion FivaAdmin activa</span>
    <h1>Catalogo de Compatibilidades para Mostrador</h1>
    <p>Base inicial lista para avanzar rapido en CRUD, importador de Excel y buscador de piezas por clave_proveedor con confirmacion desde interfaz.</p>
</div>

<div class="row compat-kpi">
    <div class="col-lg-4 col-md-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Motos registradas</h3>
                <i class='bx bx-motorcycle'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['motos'] ?? 0)) ?></h2>
                <span class="trend">Listas para relacionarse con piezas</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Piezas maestras</h3>
                <i class='bx bx-cog'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['piezas'] ?? 0)) ?></h2>
                <span class="trend">Nucleo del catalogo tecnico</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12">
        <div class="card mb-30">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Compatibilidades</h3>
                <i class='bx bx-link-alt'></i>
            </div>
            <div class="card-body">
                <h2><?= esc((string) ($kpis['compatibilidades'] ?? 0)) ?></h2>
                <span class="trend">Relaciones pieza-moto disponibles</span>
            </div>
        </div>
    </div>
</div>

<div class="card mb-30 compat-quick-search" x-data="{ q: '' }">
    <div class="card-header">
        <h3>Buscador rapido (preview)</h3>
    </div>
    <div class="card-body">
        <form class="row" hx-get="<?= site_url('/search') ?>" hx-target="#searchPreview" hx-swap="innerHTML">
            <div class="col-lg-9 col-md-8 mb-2 mb-md-0">
                <input
                    name="q"
                    type="text"
                    class="form-control"
                    placeholder="Ejemplo: balata ft150"
                    x-model="q"
                    autocomplete="off"
                >
            </div>
            <div class="col-lg-3 col-md-4">
                <button class="btn btn-primary btn-block" type="submit">Buscar</button>
            </div>
        </form>
        <div id="searchPreview" class="mt-3 text-muted">
            Endpoint pendiente de implementar en el siguiente bloque de desarrollo.
        </div>
    </div>
</div>
<?= $this->endSection() ?>
