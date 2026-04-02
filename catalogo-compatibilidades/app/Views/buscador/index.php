<?= $this->extend('layouts/fiva') ?>

<?= $this->section('content') ?>

<style>
    /* ── Variables locales ───────────────────────────────────────── */
    :root {
        --sh-orange:       #F97316;
        --sh-orange-dark:  #C2540A;
        --sh-dark-bg:      #0E1117;
        --sh-dark-card:    #161B26;
        --sh-dark-border:  rgba(255, 255, 255, .30); /* subido de .08 → borde visible */
        --sh-dark-muted:   #B0BAC8;                  /* ~7:1 sobre #0E1117 — antes .45 opacity (~4.7:1) */
    }

    /* ── Hero ───────────────────────────────────────────────────── */
    .search-hero {
        position: relative;
        overflow: hidden;
        background: var(--sh-dark-bg);
        border-radius: 18px;
        padding: 38px 36px 32px;
        margin-bottom: 28px;
        /* Noise texture overlay */
        isolation: isolate;
    }

    .search-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 55% at 0% 0%, rgba(249, 115, 22, .22) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 100% 100%, rgba(249, 115, 22, .10) 0%, transparent 60%);
        z-index: 0;
    }

    /* subtle diagonal stripes */
    .search-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: repeating-linear-gradient(-55deg,
                rgba(255, 255, 255, .018) 0px,
                rgba(255, 255, 255, .018) 1px,
                transparent 1px,
                transparent 22px);
        z-index: 0;
    }

    .search-hero>* {
        position: relative;
        z-index: 1;
    }

    /* ── Badge ──────────────────────────────────────────────────── */
    .search-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(249, 115, 22, .15);
        color: #FDBA74;
        border: 1px solid rgba(249, 115, 22, .35);
        border-radius: 999px;
        padding: 4px 12px 4px 8px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .search-badge i {
        font-size: 13px;
    }

    /* ── Title ──────────────────────────────────────────────────── */
    .search-title {
        font-size: 28px;
        font-weight: 900;
        color: #fff;
        margin-bottom: 6px;
        letter-spacing: -.5px;
        line-height: 1.15;
    }

    .search-title span {
        color: var(--sh-orange);
        position: relative;
    }

    .search-subtitle {
        font-size: 14px;           /* +0.5px para legibilidad */
        color: var(--sh-dark-muted); /* #B0BAC8 ~7:1 */
        margin-bottom: 0;
        max-width: 460px;
    }

    /* ── Tabs ───────────────────────────────────────────────────── */
    .search-tabs {
        display: flex;
        gap: 2px;
        margin: 26px 0 22px;
        background: rgba(255, 255, 255, .05);
        border-radius: 10px;
        padding: 3px;
        width: fit-content;
    }

    .search-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 20px;
        font-size: 13px;           /* +0.5px para legibilidad */
        font-weight: 600;
        color: var(--sh-dark-muted); /* #B0BAC8 ~7:1 */
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: color .15s, background .15s;
        letter-spacing: .2px;
    }

    .search-tab-btn:hover {
        color: #fff;
    }

    .search-tab-btn.active {
        background: var(--sh-orange);
        color: #fff;
        box-shadow: 0 2px 12px rgba(249, 115, 22, .45);
    }

    /* ── Text search ─────────────────────────────────────────────── */
    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: var(--sh-orange);
        pointer-events: none;
        transition: opacity .2s;
    }

    .search-input-wrap .form-control {
        padding: 14px 50px 14px 50px;
        font-size: 15.5px;
        font-weight: 500;
        border: 2px solid var(--sh-dark-border);
        border-radius: 12px;
        height: auto;
        background: rgba(255, 255, 255, .06);
        color: #fff;
        transition: border-color .2s, box-shadow .2s;
    }

    .search-input-wrap .form-control::placeholder {
        color: #7C8899; /* ~4.6:1 sobre bg oscuro — antes .28 opacity (~2.3:1) */
    }

    .search-input-wrap .form-control:focus {
        border-color: var(--sh-orange);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .18);
        background: rgba(255, 255, 255, .09);
        outline: none;
        color: #fff;
    }

    .search-input-wrap .search-spinner {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    .search-input-wrap.htmx-request .search-spinner {
        display: block;
    }

    .search-input-wrap.htmx-request .search-icon {
        opacity: .3;
    }

    .search-tip {
        font-size: 13px;   /* aumentado de 11.5px — muy pequeño para mayores */
        color: #8A95A5;    /* ~5.5:1 sobre #0E1117 — antes .25 opacity (~2.2:1, fail WCAG) */
        margin-top: 9px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .search-tip span {
        position: relative;
        padding-left: 10px;
    }

    .search-tip span::before {
        content: '›';
        position: absolute;
        left: 0;
        color: var(--sh-orange);
        opacity: .7;
    }

    /* ── Cascade search ──────────────────────────────────────────── */
    .cascade-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: end;
    }

    @media (max-width: 560px) {
        .cascade-form {
            grid-template-columns: 1fr;
        }
    }

    .cascade-field label {
        display: block;
        font-size: 12px;   /* aumentado de 10.5px — 10.5px es ilegible para mayores */
        font-weight: 700;
        color: #A0AABB;    /* ~6:1 sobre #0E1117 — antes .4 opacity (~4:1, fail WCAG AA small) */
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .cascade-field select {
        width: 100%;
        padding: 11px 36px 11px 14px;
        font-size: 15px;   /* aumentado de 13.5px para personas mayores */
        font-weight: 500;
        border: 2px solid var(--sh-dark-border); /* ahora .30 opacity — borde visible */
        border-radius: 10px;
        background-color: rgba(255, 255, 255, .08);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='rgba(249%2C115%2C22%2C.8)' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        color: #fff;
        appearance: none;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        height: 46px;
    }

    .cascade-field select option {
        background: #1E293B;
        color: #fff;
    }

    .cascade-field select:focus {
        border-color: var(--sh-orange);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .18);
        outline: none;
        background-color: rgba(255, 255, 255, .09);
    }

    .cascade-field select:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    /* estado "cargando modelos" */
    .cascade-field select.is-loading {
        opacity: .5;
        pointer-events: none;
    }

    /* ── Indicador de búsqueda activa ───────────────────────────── */
    .cascade-loading {
        display: none;
        font-size: 14px;  /* aumentado */
        color: var(--sh-dark-muted); /* #B0BAC8 ~7:1 */
        align-items: center;
        gap: 8px;
        margin-top: 12px;
    }

    /* HTMX añade .htmx-request al elemento señalado por hx-indicator */
    #cascade-indicator.htmx-request {
        display: flex;
    }

    /* ── Results container ───────────────────────────────────────── */
    #results-container {
        min-height: 60px;
    }

    [x-cloak] {
        display: none !important;
    }
</style>

<div class="search-hero" x-data="{
    tab: 'texto',
    clearResults() {
        const t = document.getElementById('empty-template');
        document.getElementById('results-container').innerHTML = t ? t.innerHTML : '';
    }
}">
    <div class="search-badge">
        <i class='bx bx-search-alt'></i> Buscador inteligente
    </div>
    <h1 class="search-title">&iquest;Qu&eacute; pieza <span>necesitas?</span></h1>
    <p class="search-subtitle">
        Busca por nombre, clave de pieza o elige directamente el modelo de tu moto.
    </p>

    <!-- Tabs -->
    <div class="search-tabs">
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'texto' }"
            @click="tab = 'texto'; clearResults()">
            <i class='bx bx-search'></i> Texto libre
        </button>
        <button
            class="search-tab-btn"
            :class="{ active: tab === 'moto' }"
            @click="tab = 'moto'; clearResults()">
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
                placeholder='Ej: "filtro de aceite", "CG125", "X-001"&hellip;'
                autocomplete="off"
                autofocus
                hx-get="<?= site_url('/search') ?>"
                hx-trigger="keyup changed delay:400ms, search"
                hx-target="#results-container"
                hx-swap="innerHTML"
                hx-indicator="#text-search-wrap">
            <span class="search-spinner">
                <span class="spinner-border spinner-border-sm" style="color:var(--sh-orange);" role="status"></span>
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
                <label><i class='bx bx-tag-alt' style="font-size:11px;margin-right:3px;"></i>Marca</label>
                <select
                    id="select-marca"
                    name="marca_id"
                    hx-get="<?= site_url('/cascada/modelos') ?>"
                    hx-trigger="change"
                    hx-target="#select-modelo"
                    hx-swap="innerHTML"
                    hx-indicator="#cascade-indicator">
                    <option value="">&#8212; Selecciona una marca &#8212;</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= (int) $m['id'] ?>"><?= esc($m['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Modelo -->
            <div class="cascade-field">
                <label><i class='bx bx-cycling' style="font-size:11px;margin-right:3px;"></i>Modelo</label>
                <select
                    id="select-modelo"
                    name="moto_id"
                    disabled
                    hx-get="<?= site_url('/search/por-moto') ?>"
                    hx-trigger="change"
                    hx-target="#results-container"
                    hx-swap="innerHTML"
                    hx-indicator="#cascade-indicator">
                    <option value="">&#8212; Primero elige una marca &#8212;</option>
                </select>
            </div>

        </div>

        <div id="cascade-indicator" class="cascade-loading">
            <span class="spinner-border spinner-border-sm" style="color:var(--sh-orange);" role="status"></span>
            <span>Cargando&hellip;</span>
        </div>
    </div>
</div>

<template id="empty-template"><?= view('buscador/_empty') ?></template>

<div id="results-container">
    <?= view('buscador/_empty') ?>
</div>

<script>
    /**
     * HTMX cascade: habilitar #select-modelo tras cargar opciones.
     * Se usa un listener global porque hx-on::afterSwap no funciona
     * (los eventos HTMX son camelCase y los atributos HTML se normalizan
     * a minúsculas — htmx:afterSwap ≠ htmx:after-swap).
     */
    (function() {
        document.addEventListener('htmx:afterSwap', function(e) {
            if (e.detail.target && e.detail.target.id === 'select-modelo') {
                var sel = e.detail.target;
                sel.disabled = false;
                sel.classList.remove('is-loading');
                // Limpiar resultados anteriores cuando cambia la marca
                document.getElementById('results-container').innerHTML = '';
            }
        });

        // Poner en estado "loading" mientras se piden los modelos
        document.addEventListener('htmx:beforeRequest', function(e) {
            if (e.detail.elt && e.detail.elt.id === 'select-marca') {
                var sel = document.getElementById('select-modelo');
                if (sel) {
                    sel.disabled = true;
                    sel.classList.add('is-loading');
                    sel.innerHTML = '<option>Cargando modelos\u2026</option>';
                }
            }
        });
    }());
</script>

<?= $this->endSection() ?>