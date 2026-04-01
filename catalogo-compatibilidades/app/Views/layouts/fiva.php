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

    <style>
        :root {
            --compat-accent: #f97316;
            --compat-accent-dark: #ea580c;
            --compat-ink: #111827;
        }

        .compat-hero {
            background: radial-gradient(circle at 15% 20%, rgba(249, 115, 22, 0.15), transparent 45%),
                        radial-gradient(circle at 80% 0%, rgba(14, 116, 144, 0.2), transparent 40%),
                        #ffffff;
            border: 1px solid rgba(17, 24, 39, 0.06);
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .compat-hero h1 {
            color: var(--compat-ink);
            letter-spacing: 0.2px;
            margin-bottom: 10px;
        }

        .compat-hero p {
            max-width: 720px;
            color: #4b5563;
            margin-bottom: 0;
        }

        .compat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(249, 115, 22, 0.12);
            color: var(--compat-accent-dark);
            border: 1px solid rgba(249, 115, 22, 0.35);
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .compat-kpi .card-header {
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }

        .compat-kpi .card-header h3 {
            font-size: 16px;
        }

        .compat-kpi .card-body h2 {
            color: var(--compat-ink);
            margin-bottom: 6px;
        }

        .compat-kpi .trend {
            color: var(--compat-accent-dark);
            font-weight: 600;
            font-size: 13px;
        }

        .compat-quick-search .btn {
            background: linear-gradient(135deg, var(--compat-accent), var(--compat-accent-dark));
            border: 0;
        }

        .compat-quick-search .btn:hover {
            filter: brightness(0.98);
        }
    </style>
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
                <li class="nav-item-title">MVP</li>
                <li class="nav-item">
                    <a href="<?= site_url('/') ?>" class="nav-link">
                        <span class="icon"><i class='bx bx-home-circle'></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="icon"><i class='bx bx-search-alt'></i></span>
                        <span class="menu-title">Buscador</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="icon"><i class='bx bx-cog'></i></span>
                        <span class="menu-title">Compatibilidades</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
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

    <script src="<?= base_url('fiva-assets/js/vendors.min.js') ?>"></script>
    <script src="<?= base_url('fiva-assets/js/custom.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</body>
</html>
