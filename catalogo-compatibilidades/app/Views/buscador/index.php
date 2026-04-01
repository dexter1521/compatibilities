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

    .search-input-wrap {
        position: relative;
        margin-top: 24px;
    }

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

    .search-tip span::before {
        content: '— ';
    }

    #results-container {
        min-height: 60px;
    }
</style>

<div class="search-hero">
    <div class="search-label">
        <i class='bx bx-search-alt'></i> Buscador inteligente
    </div>
    <h1 class="search-title">¿Qué pieza necesitas?</h1>
    <p class="search-subtitle">
        Busca por nombre de pieza, clave de proveedor o modelo de moto.<br>
        Los resultados incluyen compatibilidades verificadas y claves equivalentes.
    </p>

    <div class="search-input-wrap">
        <i class='bx bx-search search-icon'></i>
        <input
            id="q"
            name="q"
            type="search"
            class="form-control"
            placeholder='Ej: "filtro aceite", "CG125", "X-001"…'
            autocomplete="off"
            autofocus
            hx-get="<?= site_url('/search') ?>"
            hx-trigger="keyup changed delay:400ms, search"
            hx-target="#results-container"
            hx-swap="innerHTML"
            hx-indicator=".search-input-wrap"
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

<div id="results-container">
    <?= view('buscador/_empty') ?>
</div>

<?= $this->endSection() ?>
