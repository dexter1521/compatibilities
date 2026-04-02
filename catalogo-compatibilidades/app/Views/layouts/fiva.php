<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/vendors.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/responsive.css') ?>">

    <title><?= esc($title ?? 'Compatibilidades') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
</head>
<body>
    <div class="sidemenu-area">
        <div class="sidemenu-header">
            <a href="<?= site_url('/') ?>" class="navbar-brand d-flex align-items-center">
                <img src="<?= base_url('fiva-assets/img/small-logo.png') ?>" alt="logo">
                <span>Compatibilidades</span>
            </a>

            <div class="burger-menu d-none d-lg-block">
                <span class="top-bar"></span>
                <span class="middle-bar"></span>
                <span class="bottom-bar"></span>
            </div>

            <div class="responsive-burger-menu d-block d-lg-none">
                <span class="top-bar"></span>
                <span class="middle-bar"></span>
                <span class="bottom-bar"></span>
            </div>
        </div>

        <div class="sidemenu-body">
            <ul class="sidemenu-nav metisMenu h-100" id="sidemenu-nav" data-simplebar="">
<?php $seg1 = service('uri')->getSegment(1); ?>
                <li class="nav-item-title">Principal</li>
                <li class="nav-item <?= $seg1 === '' ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-home-circle'></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item <?= $seg1 === 'buscador' ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/buscador') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-search-alt'></i></span>
                        <span class="menu-title">Buscador</span>
                    </a>
                </li>

                <li class="nav-item-title">Catálogo</li>
                <li class="nav-item <?= $seg1 === 'motos' ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/motos') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-car'></i></span>
                        <span class="menu-title">Motocicletas</span>
                    </a>
                </li>
                <li class="nav-item <?= $seg1 === 'piezas' ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/piezas') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-wrench'></i></span>
                        <span class="menu-title">Piezas Maestras</span>
                    </a>
                </li>
                <li class="nav-item <?= $seg1 === 'compatibilidades' ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/compatibilidades') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-link-alt'></i></span>
                        <span class="menu-title">Compatibilidades</span>
                    </a>
                </li>

                <li class="nav-item-title">Importación</li>
                <li class="nav-item <?= ($seg1 === 'import') ? 'mm-active' : '' ?>">
                    <a href="<?= site_url('/import') ?>" class="nav-link <?= ($seg1 === 'import') ? 'active' : '' ?>">
                        <span class="icon"><i class='bx bx-upload'></i></span>
                        <span class="menu-title">Importador</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content d-flex flex-column">
        <div class="top-navbar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-9 col-md-8">
                        <div class="nav-title">
                            <h3><?= esc($pageTitle ?? 'Panel') ?></h3>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 text-right">
                        <span class="badge badge-primary">MVP v0.1</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content-container overflow-hidden">
            <div class="container-fluid">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- ── Modal Global CRUD ─────────────────────────────────────────── -->
    <div class="modal fade" id="crud-modal" tabindex="-1" aria-hidden="true" aria-labelledby="crud-modal-label">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modal-content"></div>
        </div>
    </div>

    <script src="<?= base_url('fiva-assets/js/vendors.min.js') ?>"></script>
    <script src="<?= base_url('fiva-assets/js/custom.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        // ── CSRF ──────────────────────────────────────────────────────
        document.addEventListener('htmx:configRequest', function (e) {
            e.detail.headers['<?= csrf_header() ?>'] = '<?= csrf_hash() ?>';
        });
        document.addEventListener('htmx:afterRequest', function (e) {
            var newToken = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                document.querySelectorAll('input[name="<?= csrf_field() ?>"]').forEach(function(el) {
                    el.value = newToken;
                });
            }
        });

        // ── Modal CRUD: abrir cuando HTMX cargue contenido en #modal-content ──
        document.addEventListener('htmx:afterSwap', function (evt) {
            if (evt.target.id === 'modal-content' && evt.target.innerHTML.trim() !== '') {
                $('#crud-modal').modal('show');
            }
        });
        // Cerrar modal por header HX-Trigger: closeModal
        document.addEventListener('closeModal', function () {
            $('#crud-modal').modal('hide');
        });
        // Limpiar modal-content al cerrarse para evitar estado sucio
        document.getElementById('crud-modal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modal-content').innerHTML = '';
        });
    </script>
</body>
</html>
