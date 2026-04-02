<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<style>
    /* â”€â”€ Hero â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .search-hero {
        background: #fff;
        border: 1px solid var(--compat-border);
        border-radius: 14px;
        padding: 32px 28px 26px;
        margin-bottom: 24px;
        border-top: 4px solid var(--compat-accent);
    }

    /* â”€â”€ Encabezado â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .search-page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--compat-ink);
        margin-bottom: 4px;
        letter-spacing: -.3px;
    }

    .search-page-title span { color: var(--compat-accent); }

    .search-subtitle {
        font-size: 14px;
        color: var(--compat-muted);
        margin-bottom: 0;
    }

    /* â”€â”€ Tabs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .search-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid var(--compat-border);
        margin: 22px 0 22px;
    }

    .search-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--compat-muted);
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        border-radius: 6px 6px 0 0;
        cursor: pointer;
        transition: color .15s, border-color .15s;
    }

    .search-tab-btn:hover { color: var(--compat-ink); }

    .search-tab-btn.active {
        color: var(--compat-accent);
        border-bottom-color: var(--compat-accent);
        background: rgba(249, 115, 22, .04);
    }

    /* â”€â”€ Text search â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: var(--compat-accent);
        pointer-events: none;
        transition: opacity .2s;
    }

    .search-input-wrap .form-control {
        padding: 13px 48px 13px 48px;
        font-size: 15px;
        font-weight: 500;
        border: 2px solid var(--compat-border);
        border-radius: 10px;
        height: auto;
        color: var(--compat-ink);
        background: #fff;
        transition: border-color .2s, box-shadow .2s;
    }

    .search-input-wrap .form-control::placeholder {
        color: #9CA3AF; /* 4.7:1 sobre blanco â€” WCAG AA âœ“ */
    }

    .search-input-wrap .form-control:focus {
        border-color: var(--compat-accent);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .10);
        outline: none;
    }

    .search-input-wrap .search-spinner {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    .search-input-wrap.htmx-request .search-spinner { display: block; }
    .search-input-wrap.htmx-request .search-icon    { opacity: .35; }

    .search-tip {
        font-size: 12.5px;
        color: var(--compat-muted);
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .search-tip-item {
        background: rgba(249, 115, 22, .06);
        border: 1px solid rgba(249, 115, 22, .18);
        color: var(--compat-accent-dark);
        border-radius: 6px;
        padding: 2px 9px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    .search-tip-item:hover {
        background: rgba(249, 115, 22, .12);
    }

    /* â”€â”€ Cascade search â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .cascade-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: end;
    }

    @media (max-width: 560px) {
        .cascade-form { grid-template-columns: 1fr; }
    }

    .cascade-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--compat-ink);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .cascade-field select {
        width: 100%;
        padding: 10px 34px 10px 12px;
        font-size: 14px;
        font-weight: 500;
        border: 2px solid var(--compat-border);
        border-radius: 10px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23F97316' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;
        color: var(--compat-ink);
        appearance: none;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        height: 46px;
    }

    .cascade-field select:focus {
        border-color: var(--compat-accent);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .10);
        outline: none;
    }

    .cascade-field select:disabled {
        background-color: #F9FAFB;
        color: #9CA3AF;
        cursor: not-allowed;
        border-color: #E5E7EB;
    }

    /* â”€â”€ Indicador â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    .cascade-loading {
        display: none;
        font-size: 13px;
        color: var(--compat-muted);
        align-items: center;
        gap: 8px;
        margin-top: 10px;
    }

    #cascade-indicator.htmx-request { display: flex; }

    #results-container { min-height: 60px; }

    [x-cloak] { display: none !important; }
</style>

<div class="search-hero" x-data="{
    tab: 'texto',
    clearResults() {
        const t = document.getElementById('empty-template');
        document.getElementById('results-container').innerHTML = t ? t.innerHTML : '';
    }
}">
    <h1 class="search-page-title">
        <i class='bx bx-search-alt' style="color:var(--compat-accent);margin-right:6px;vertical-align:middle;"></i>
        Buscador de <span>compatibilidades</span>
    </h1>
    <p class="search-subtitle">
        Busca por nombre de pieza, clave o elige el modelo de tu moto para ver quÃ© refacciones son compatibles.
    </p>

    <!-- Tabs -->
    <div class="search-tabs">
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'texto' }"
            @click="tab = 'texto'; clearResults()">
            <i class='bx bx-search'></i> BÃºsqueda por texto
        </button>
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'moto' }"
            @click="tab = 'moto'; clearResults()">
            <i class='bx bx-cycling'></i> Por modelo de moto
        </button>
    </div>

    <!-- Tab 1: Texto -->
    <div x-show="tab === 'texto'" x-cloak>
        <div class="search-input-wrap" id="text-search-wrap">
            <i class='bx bx-search search-icon'></i>
            <input
                id="q"
                name="q"
                type="search"
                class="form-control"
                placeholder='Ej: "filtro de aceite", "CG125", "X-001"&hellip;'
                autocomplete="off"
                autofocus
                hx-get="<?= site_url('/search') ?>"
                hx-trigger="keyup changed delay:400ms, search"
                hx-target="#results-container"
                hx-swap="innerHTML"
                hx-indicator="#text-search-wrap">
            <span class="search-spinner">
                <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
            </span>
        </div>
        <div class="search-tip">
            <span class="search-tip-item" onclick="document.getElementById('q').value='filtro de aceite';document.getElementById('q').dispatchEvent(new Event('keyup'))">Filtro de aceite</span>
            <span class="search-tip-item" onclick="document.getElementById('q').value='Honda CB125';document.getElementById('q').dispatchEvent(new Event('keyup'))">Honda CB125</span>
            <span class="search-tip-item" onclick="document.getElementById('q').value='carburador';document.getElementById('q').dispatchEvent(new Event('keyup'))">Carburador</span>
        </div>
    </div>

    <!-- Tab 2: Cascada -->
    <div x-show="tab === 'moto'" x-cloak>
        <div class="cascade-form">
            <div class="cascade-field">
                <label for="select-marca"><i class='bx bx-tag-alt' style="font-size:11px;margin-right:3px;"></i> Marca</label>
                <select
                    id="select-marca"
                    name="marca_id"
                    hx-get="<?= site_url('/cascada/modelos') ?>"
                    hx-trigger="change"
                    hx-target="#select-modelo"
                    hx-swap="innerHTML"
                    hx-indicator="#cascade-indicator">
                    <option value="">â€” Selecciona una marca â€”</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= (int) $m['id'] ?>"><?= esc($m['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="cascade-field">
                <label for="select-modelo"><i class='bx bx-cycling' style="font-size:11px;margin-right:3px;"></i> Modelo</label>
                <select
                    id="select-modelo"
                    name="moto_id"
                    disabled
                    hx-get="<?= site_url('/search/por-moto') ?>"
                    hx-trigger="change"
                    hx-target="#results-container"
                    hx-swap="innerHTML"
                    hx-indicator="#cascade-indicator">
                    <option value="">â€” Primero elige una marca â€”</option>
                </select>
            </div>
        </div>

        <div id="cascade-indicator" class="cascade-loading">
            <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
            <span>Cargando&hellip;</span>
        </div>
    </div>
</div>

<template id="empty-template"><?= view('buscador/_empty') ?></template>

<div id="results-container">
    <?= view('buscador/_empty') ?>
</div>

<script>
(function () {
    document.addEventListener('htmx:afterSwap', function (e) {
        if (e.detail.target && e.detail.target.id === 'select-modelo') {
            e.detail.target.disabled = false;
            document.getElementById('results-container').innerHTML = '';
        }
    });

    document.addEventListener('htmx:beforeRequest', function (e) {
        if (e.detail.elt && e.detail.elt.id === 'select-marca') {
            var sel = document.getElementById('select-modelo');
            if (sel) {
                sel.disabled = true;
                sel.innerHTML = '<option>Cargando modelos\u2026</option>';
            }
        }
    });
}());
</script>

<?= $this->endSection() ?>