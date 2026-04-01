<div class="d-flex flex-column align-items-center justify-content-center py-5 text-center">
    <div style="
        width: 72px; height: 72px;
        background: rgba(249,115,22,.1);
        border-radius: 999px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    ">
        <i class='bx bx-search-alt' style="font-size:32px; color: var(--compat-accent);"></i>
    </div>
    <h4 style="font-size:17px; font-weight:700; color:var(--compat-ink); margin-bottom:6px;">
        Sin resultados para "<?= esc($q) ?>"
    </h4>
    <p style="font-size:13.5px; color:#6b7280; max-width:400px; margin-bottom:20px;">
        No encontramos piezas, claves ni motos que coincidan.<br>
        Tu búsqueda quedó registrada para que podamos agregarla.
    </p>
    <div style="
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(249,115,22,.08);
        border: 1px solid rgba(249,115,22,.25);
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 12.5px;
        color: var(--compat-accent-dark);
        font-weight: 600;
    ">
        <i class='bx bx-pin'></i>
        Búsqueda registrada como no encontrada
    </div>
</div>
