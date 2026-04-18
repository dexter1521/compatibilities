<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<style>
    .crud-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .crud-page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--compat-ink);
        margin: 0;
        letter-spacing: -.3px;
    }

    .crud-page-title span {
        color: var(--compat-accent);
    }

    .btn-crud-new {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: linear-gradient(135deg, var(--compat-accent), var(--compat-accent-dark));
        color: #fff;
        border: 0;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: filter .15s;
        text-decoration: none;
    }

    .btn-crud-new:hover {
        filter: brightness(.96);
        color: #fff;
        text-decoration: none;
    }

    .crud-card {
        background: #fff;
        border: 1px solid rgba(17, 24, 39, .08);
        border-radius: 14px;
        overflow: hidden;
    }

    .crud-card-toolbar {
        padding: 14px 20px;
        border-bottom: 1px solid rgba(17, 24, 39, .07);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .crud-search-input {
        border: 1.5px solid rgba(17, 24, 39, .12);
        border-radius: 8px;
        padding: 7px 14px 7px 36px;
        font-size: 13px;
        width: 240px;
        transition: border-color .2s;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat 10px center;
    }

    .crud-search-input:focus {
        border-color: var(--compat-accent);
        outline: none;
    }

    .crud-count-badge {
        font-size: 12px;
        color: #6b7280;
        margin-left: auto;
    }

    .crud-table {
        width: 100%;
        border-collapse: collapse;
    }

    .crud-table thead th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6b7280;
        padding: 11px 16px;
        border-bottom: 1px solid rgba(17, 24, 39, .09);
        background: rgba(249, 115, 22, .03);
    }

    .crud-table tbody td {
        padding: 11px 16px;
        font-size: 13.5px;
        color: var(--compat-ink);
        border-bottom: 1px solid rgba(17, 24, 39, .05);
        vertical-align: middle;
    }

    .crud-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .crud-table tbody tr:hover {
        background: rgba(249, 115, 22, .03);
    }

    .btn-tbl-edit,
    .btn-tbl-del {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 0;
        cursor: pointer;
        font-size: 15px;
        transition: background .15s;
    }

    .btn-tbl-edit {
        background: rgba(14, 116, 144, .09);
        color: #0e7490;
    }

    .btn-tbl-edit:hover {
        background: rgba(14, 116, 144, .18);
    }

    .btn-tbl-del {
        background: rgba(220, 38, 38, .08);
        color: #dc2626;
        margin-left: 4px;
    }

    .btn-tbl-del:hover {
        background: rgba(220, 38, 38, .16);
    }

    .empty-state {
        text-align: center;
        padding: 48px 0;
        color: #9ca3af;
        font-size: 14px;
    }

    .empty-state i {
        font-size: 40px;
        display: block;
        margin-bottom: 10px;
    }
</style>

<div class="crud-page-header">
    <h1 class="crud-page-title">
        <i class='bx bx-tag-alt' style="color:var(--compat-accent);margin-right:8px;vertical-align:middle;"></i>
        Marcas <span>(<?= count($marcas) ?>)</span>
    </h1>
    <button
        class="btn-crud-new"
        hx-get="<?= site_url('/marcas/create') ?>"
        hx-target="#modal-content"
        hx-swap="innerHTML">
        <i class='bx bx-plus'></i> Nueva Marca
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class='bx bx-check-circle'></i> <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class='bx bx-error-circle'></i> <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif ?>

<div class="crud-card" x-data="{ busq: '' }">
    <div class="crud-card-toolbar">
        <input type="search" class="crud-search-input" placeholder="Filtrar por nombre…" x-model="busq">
        <span class="crud-count-badge" x-text="busq ? 'Filtrando…' : '<?= count($marcas) ?> registros'"></span>
    </div>
    <div style="overflow-x:auto;">
        <table class="crud-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th style="text-align:center;">Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="marcas-tbody">
                <?= view('marcas/_rows', ['marcas' => $marcas]) ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>