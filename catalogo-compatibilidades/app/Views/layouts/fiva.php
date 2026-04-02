<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/vendors.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('fiva-assets/css/responsive.css') ?>">
    <style>
        /* Variables globales del sistema de diseño */
        :root {
            --compat-accent:      #F97316;   /* naranja Shark Motors */
            --compat-accent-dark: #C2540A;
            --compat-ink:         #2a2a2a;   /* texto principal Fiva */
            --compat-muted:       #6B7280;   /* texto secundario ~7:1 sobre blanco */
            --compat-border:      #E5E7EB;
            --compat-bg:          #fafafa;
        }
    </style>

    <title><?= esc($title ?? 'Compatibilidades') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
</head>

<body>
    <div class="sidemenu-area">
        <div class="sidemenu-header">
            <a href="<?= site_url('/') ?>" class="navbar-brand d-flex align-items-center">
                <img src="<?= base_url('fiva-assets/img/small-logo.png') ?>" alt="logo">
                <span>SM</span>
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
        <!-- Top Navbar Area -->
        <nav class="navbar top-navbar navbar-expand">
            <div class="collapse navbar-collapse" id="navbarSupportContent">
                <div class="responsive-burger-menu d-block d-lg-none">
                    <span class="top-bar"></span>
                    <span class="middle-bar"></span>
                    <span class="bottom-bar"></span>
                </div>

                <ul class="navbar-nav left-nav align-items-center">
                    <li class="nav-item">
                        <a href="app-email.html" class="nav-link" data-toggle="tooltip" data-placement="bottom" title="Email">
                            <i class="bx bx-envelope"></i>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="app-chat.html" class="nav-link" data-toggle="tooltip" data-placement="bottom" title="Message">
                            <i class='bx bx-message'></i>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="app-calendar.html" class="nav-link" data-toggle="tooltip" data-placement="bottom" title="Calendar">
                            <i class='bx bx-calendar'></i>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="app-todo.html" class="nav-link" data-toggle="tooltip" data-placement="bottom" title="Todo List">
                            <i class='bx bx-edit'></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown apps-box">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class='bx bxs-grid'></i>
                        </a>

                        <div class="dropdown-menu">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span class="title d-inline-block">Web Apps</span>
                                <span class="edit-btn d-inline-block">Edit</span>
                            </div>

                            <div class="dropdown-body">
                                <div class="d-flex flex-wrap align-items-center">
                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-account.png" alt="image">
                                        <span class="d-block mb-0">Account</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-google.png" alt="image">
                                        <span class="d-block mb-0">Search</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-map.png" alt="image">
                                        <span class="d-block mb-0">Maps</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-youtube.png" alt="image">
                                        <span class="d-block mb-0">YouTube</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-playstore.png" alt="image">
                                        <span class="d-block mb-0">Play</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-gmail.png" alt="image">
                                        <span class="d-block mb-0">Gmail</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-drive.png" alt="image">
                                        <span class="d-block mb-0">Drive</span>
                                    </a>

                                    <a href="#" class="dropdown-item">
                                        <img src="assets\img\icon-calendar.png" alt="image">
                                        <span class="d-block mb-0">Calendar</span>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown-footer">
                                <a href="#" class="dropdown-item">View All</a>
                            </div>
                        </div>
                    </li>
                </ul>

                <form class="nav-search-form d-none ml-auto d-md-block">
                    <label><i class='bx bx-search'></i></label>
                    <input type="text" class="form-control" placeholder="Search here...">
                </form>

                <ul class="navbar-nav right-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link bx-fullscreen-btn" id="fullscreen-button">
                            <i class="bx bx-fullscreen"></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown language-switch-nav-item">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="assets\img\us-flag.jpg" alt="image">
                            <span>English <i class='bx bx-chevron-down'></i></span>
                        </a>

                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span>German</span>

                                <img src="assets\img\germany-flag.jpg" alt="flag">
                            </a>

                            <a href="#" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span>French</span>

                                <img src="assets\img\france-flag.jpg" alt="flag">
                            </a>

                            <a href="#" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span>Spanish</span>

                                <img src="assets\img\spain-flag.jpg" alt="flag">
                            </a>

                            <a href="#" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span>Russian</span>

                                <img src="assets\img\russia-flag.jpg" alt="flag">
                            </a>

                            <a href="#" class="dropdown-item d-flex justify-content-between align-items-center">
                                <span>Italian</span>

                                <img src="assets\img\italy-flag.jpg" alt="flag">
                            </a>
                        </div>
                    </li>

                    <li class="nav-item message-box dropdown">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="message-btn">
                                <i class='bx bx-envelope'></i>

                                <span class="badge badge-primary">4</span>
                            </div>
                        </a>

                        <div class="dropdown-menu">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span class="title d-inline-block">4 New Message</span>
                                <span class="clear-all-btn d-inline-block">Clear All</span>
                            </div>

                            <div class="dropdown-body">
                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="figure">
                                        <img src="assets\img\user1.jpg" class="rounded-circle" alt="image">
                                    </div>

                                    <div class="content d-flex justify-content-between align-items-center">
                                        <div class="text">
                                            <span class="d-block">Sarah Taylor</span>
                                            <p class="sub-text mb-0">UX/UI design</p>
                                        </div>
                                        <p class="time-text mb-0">2 sec ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="figure">
                                        <img src="assets\img\user2.jpg" class="rounded-circle" alt="image">
                                    </div>

                                    <div class="content d-flex justify-content-between align-items-center">
                                        <div class="text">
                                            <span class="d-block">Lucy Eva</span>
                                            <p class="sub-text mb-0">Web developers</p>
                                        </div>
                                        <p class="time-text mb-0">5 sec ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="figure">
                                        <img src="assets\img\user3.jpg" class="rounded-circle" alt="image">
                                    </div>

                                    <div class="content d-flex justify-content-between align-items-center">
                                        <div class="text">
                                            <span class="d-block">James Anderson</span>
                                            <p class="sub-text mb-0">Content whitter</p>
                                        </div>
                                        <p class="time-text mb-0">3 min ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="figure">
                                        <img src="assets\img\user4.jpg" class="rounded-circle" alt="image">
                                    </div>

                                    <div class="content d-flex justify-content-between align-items-center">
                                        <div class="text">
                                            <span class="d-block">Steven Smith</span>
                                            <p class="sub-text mb-0">Digital marketing</p>
                                        </div>
                                        <p class="time-text mb-0">7 min ago</p>
                                    </div>
                                </a>
                            </div>

                            <div class="dropdown-footer">
                                <a href="#" class="dropdown-item">View All</a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item notification-box dropdown">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="notification-btn">
                                <i class='bx bx-bell'></i>

                                <span class="badge badge-secondary">5</span>
                            </div>
                        </a>

                        <div class="dropdown-menu">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span class="title d-inline-block">6 New Notifications</span>
                                <span class="mark-all-btn d-inline-block">Mark all as read</span>
                            </div>

                            <div class="dropdown-body">
                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="icon">
                                        <i class='bx bx-message-rounded-dots'></i>
                                    </div>

                                    <div class="content">
                                        <span class="d-block">Just sent a new message!</span>
                                        <p class="sub-text mb-0">2 sec ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="icon">
                                        <i class='bx bx-user'></i>
                                    </div>

                                    <div class="content">
                                        <span class="d-block">New customer registered</span>
                                        <p class="sub-text mb-0">5 sec ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="icon">
                                        <i class='bx bx-layer'></i>
                                    </div>

                                    <div class="content">
                                        <span class="d-block">Apps are ready for update</span>
                                        <p class="sub-text mb-0">3 min ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="icon">
                                        <i class='bx bx-hourglass'></i>
                                    </div>

                                    <div class="content">
                                        <span class="d-block">Your item is shipped</span>
                                        <p class="sub-text mb-0">7 min ago</p>
                                    </div>
                                </a>

                                <a href="#" class="dropdown-item d-flex align-items-center">
                                    <div class="icon">
                                        <i class='bx bx-comment-dots'></i>
                                    </div>

                                    <div class="content">
                                        <span class="d-block">Steven commented on your post</span>
                                        <p class="sub-text mb-0">1 sec ago</p>
                                    </div>
                                </a>
                            </div>

                            <div class="dropdown-footer">
                                <a href="#" class="dropdown-item">View All</a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown profile-nav-item">
                        <a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="menu-profile">
                                <span class="name">Hi! Andro</span>
                                <img src="assets\img\user1.jpg" class="rounded-circle" alt="image">
                            </div>
                        </a>

                        <div class="dropdown-menu">
                            <div class="dropdown-header d-flex flex-column align-items-center">
                                <div class="figure mb-3">
                                    <img src="assets\img\user1.jpg" class="rounded-circle" alt="image">
                                </div>

                                <div class="info text-center">
                                    <span class="name">Andro Smith</span>
                                    <p class="mb-3 email">hello@androsmith.com</p>
                                </div>
                            </div>

                            <div class="dropdown-body">
                                <ul class="profile-nav p-0 pt-3">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class='bx bx-user'></i> <span>Profile</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class='bx bx-envelope'></i> <span>My Inbox</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class='bx bx-edit-alt'></i> <span>Edit Profile</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class='bx bx-cog'></i> <span>Settings</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="dropdown-footer">
                                <ul class="profile-nav">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class='bx bx-log-out'></i> <span>Logout</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- End Top Navbar Area -->

        <!-- Breadcrumb Area -->
        <div class="breadcrumb-area">
            <h1>Dashboard</h1>

            <ol class="breadcrumb">
                <li class="item"><a href="dashboard-analytics.html"><i class='bx bx-home-alt'></i></a></li>

                <li class="item">Dashboard</li>

                <li class="item">Analytics</li>
            </ol>
        </div>
        <!-- End Breadcrumb Area -->

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
        // ── CSRF + marcar como AJAX para que CI4 omita DebugToolbar ──
        document.addEventListener('htmx:configRequest', function(e) {
            e.detail.headers['<?= csrf_header() ?>'] = '<?= csrf_hash() ?>';
            e.detail.headers['X-Requested-With'] = 'XMLHttpRequest';
        });
        document.addEventListener('htmx:afterRequest', function(e) {
            var newToken = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                document.querySelectorAll('input[name="<?= csrf_field() ?>"]').forEach(function(el) {
                    el.value = newToken;
                });
            }
        });

        // ── Modal CRUD: abrir cuando HTMX cargue contenido en #modal-content ──
        document.addEventListener('htmx:afterSwap', function(evt) {
            if (evt.target.id === 'modal-content' && evt.target.innerHTML.trim() !== '') {
                $('#crud-modal').modal('show');
            }
        });
        // Cerrar modal por header HX-Trigger: closeModal
        document.addEventListener('closeModal', function() {
            $('#crud-modal').modal('hide');
        });
        // Limpiar modal-content al cerrarse para evitar estado sucio
        document.getElementById('crud-modal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modal-content').innerHTML = '';
        });
    </script>
</body>

</html>