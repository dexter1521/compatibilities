<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<style>
    .search-hero {
        background: radial-gradient(ellipse at 8% 10%, rgba(249,115,22,.18) 0%, transparent 50%),
                    radial-gradient(ellipse at 92% 90%, rgba(14,116,144,.14) 0%, transparent 50%),
                    #fff;
        border: 1px solid rgba(17,24,39,.07);
        border-radius: 16px;
        padding: 36px 32px 28px;
        margin-bottom: 28px;
    }

    .search-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(249,115,22,.1);
        color: var(--compat-accent-dark);
        border: 1px solid rgba(249,115,22,.3);
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .search-title {
        font-size: 26px;
        font-weight: 800;
        color: var(--compat-ink);
        margin-bottom: 6px;
        letter-spacing: -.3px;
    }

    .search-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    /* ── Tabs ──────────────────────────────────────────────── */
    .search-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid rgba(17,24,39,.08);
        margin: 22px 0 24px;
    }

    .search-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
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
        background: rgba(249,115,22,.05);
    }

    /* ── Text search ───────────────────────────────────────── */
    .search-input-wrap { position: relative; }

    .search-input-wrap .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: var(--compat-accent);
        pointer-events: none;
    }

    .search-input-wrap .form-control {
        padding: 14px 16px 14px 50px;
        font-size: 16px;
        font-weight: 500;
        border: 2px solid rgba(17,24,39,.12);
        border-radius: 12px;
        height: auto;
        transition: border-color .2s, box-shadow .2s;
    }

    .search-input-wrap .form-control:focus {
        border-color: var(--compat-accent);
        box-shadow: 0 0 0 3px rgba(249,115,22,.12);
        outline: none;
    }

    .search-input-wrap .search-spinner {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    .htmx-request .search-spinner { display: block; }
    .htmx-request .search-icon    { opacity: .4; }

    .search-tip {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 8px;
        display: flex;
        gap: 16px;
    }

    .search-tip span::before { content: '— '; }

    /* ── Cascade search ─────────────────────────────────────── */
    .cascade-form {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .cascade-field label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 5px;
    }

    .cascade-field select {
        width: 100%;
        padding: 10px 34px 10px 14px;
        font-size: 13.5px;
        font-weight: 500;
        border: 2px solid rgba(17,24,39,.12);
        border-radius: 10px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;
        color: var(--compat-ink);
        appearance: none;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        height: 44px;
    }

    .cascade-field select:focus {
        border-color: var(--compat-accent);
        box-shadow: 0 0 0 3px rgba(249,115,22,.12);
        outline: none;
    }

    .cascade-field select:disabled {
        background-color: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .cascade-btn {
        padding: 10px 24px;
        height: 44px;
        background: var(--compat-accent);
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .2s, opacity .2s;
        white-space: nowrap;
    }

    .cascade-btn:disabled { opacity: .5; cursor: not-allowed; }
    .cascade-btn:not(:disabled):hover { background: var(--compat-accent-dark); }

    .cascade-loading {
        display: none;
        font-size: 12px;
        color: #9ca3af;
        align-items: center;
        gap: 6px;
    }

    .htmx-request .cascade-loading { display: flex; }

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
    <div class="search-label">
        <i class='bx bx-search-alt'></i> Buscador inteligente
    </div>
    <h1 class="search-title">&iquest;Qu&eacute; pieza necesitas?</h1>
    <p class="search-subtitle">
        Busca por nombre de pieza o selecciona el modelo de tu moto para ver las refacciones compatibles.
    </p>

    <!-- Tabs -->
    <div class="search-tabs">
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'texto' }"
            @click="tab = 'texto'; clearResults()"
        >
            <i class='bx bx-search'></i> Texto libre
        </button>
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'moto' }"
            @click="tab = 'moto'; clearResults()"
        >
            <i class='bx bx-cycling'></i> Por modelo de moto
        </button>
    </div>

    <!-- Tab 1: Text search -->
    <div x-show="tab === 'texto'" x-cloak>
        <div class="search-input-wrap" id="text-search-wrap">
            <i class='bx bx-search search-icon'></i>
            <input
                id="q"
                name="q"
                type="search"
                class="form-control"
                placeholder='Ej: "filtro aceite", "CG125", "X-001"&hellip;'
                autocomplete="off"
                autofocus
                hx-get="<?= site_url('/search') ?>"
                hx-trigger="keyup changed delay:400ms, search"
                hx-target="#results-container"
                hx-swap="innerHTML"
                hx-indicator="#text-search-wrap"
            >
            <span class="search-spinner">
                <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
            </span>
        </div>
        <p class="search-tip">
            <span>Filtro de aceite</span>
            <span>Honda CB125</span>
            <span>AX-100 Carburador</span>
        </p>
    </div>

    <!-- Tab 2: Cascade search -->
    <div x-show="tab === 'moto'" x-cloak>
        <div class="cascade-form">

            <!-- Marca -->
            <div class="cascade-field">
                <label>Marca</label>
                <select
                    name="marca_id"
                    hx-get="<?= site_url('/cascada/modelos') ?>"
                    hx-trigger="change"
                    hx-target="#select-modelo"
                    hx-swap="innerHTML"
                    hx-on::after-swap="
                        document.getElementById('select-modelo').disabled = false;
                        document.getElementById('results-container').innerHTML = '';
                    "
                    hx-on::before-request="
                        var s = document.getElementById('select-modelo');
                        s.disabled = true;
                        s.innerHTML = '<option>Cargando&hellip;</option>';
                        document.getElementById('results-container').innerHTML = '';
                    "
                >
                    <option value="">&#8212; Selecciona marca &#8212;</option>
                    <?php foreach ($marcas as $m): ?>
                    <option value="<?= (int) $m['id'] ?>"><?= esc($m['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Modelo (cargado por HTMX, busca al cambiar) -->
            <div class="cascade-field">
                <label>Modelo</label>
                <select
                    id="select-modelo"
                    name="moto_id"
                    disabled
                    hx-get="<?= site_url('/search/por-moto') ?>"
                    hx-trigger="change"
                    hx-target="#results-container"
                    hx-swap="innerHTML"
                    hx-indicator="#cascade-indicator"
                >
                    <option value="">&#8212; Primero elige marca &#8212;</option>
                </select>
            </div>

        </div>

        <div id="cascade-indicator" class="cascade-loading mt-2">
            <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
            Buscando refacciones&hellip;
        </div>
    </div>
</div>

<template id="empty-template"><?= view('buscador/_empty') ?></template>

<div id="results-container">
    <?= view('buscador/_empty') ?>
</div>

<?= $this->endSection() ?>
