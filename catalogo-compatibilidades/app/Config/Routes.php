<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/docs/api', 'ApiDocs::index');
$routes->get('/docs/openapi.yaml', 'ApiDocs::spec');

// Buscador
$routes->get('/buscador',          'Search::index');
$routes->get('/search',            'Search::results');
$routes->get('/search/por-moto',   'Search::porMoto');
$routes->get('/cascada/modelos',   'Search::cascadaModelos');

// ── CRUD Motocicletas ──────────────────────────────────────────
$routes->get('/motos',                 'Motos::index');
$routes->get('/motos/create',          'Motos::create');
$routes->post('/motos/store',          'Motos::store');
$routes->get('/motos/(:num)/edit',     'Motos::edit/$1');
$routes->post('/motos/(:num)/update',  'Motos::update/$1');
$routes->post('/motos/(:num)/delete',  'Motos::delete/$1');
$routes->get('/motos/(:num)/aliases',         'Motos::aliases/$1');
$routes->post('/motos/(:num)/aliases/store',  'Motos::storeAlias/$1');
$routes->post('/motos/alias/(:num)/delete',   'Motos::deleteAlias/$1');

// ── CRUD Marcas ────────────────────────────────────────────────
$routes->get('/marcas',                'Marcas::index');
$routes->get('/marcas/create',         'Marcas::create');
$routes->post('/marcas/store',         'Marcas::store');
$routes->get('/marcas/(:num)/edit',    'Marcas::edit/$1');
$routes->post('/marcas/(:num)/update', 'Marcas::update/$1');
$routes->post('/marcas/(:num)/toggle', 'Marcas::toggle/$1');
$routes->post('/marcas/(:num)/delete', 'Marcas::delete/$1');

// ── CRUD Piezas Maestras ───────────────────────────────────────
$routes->get('/piezas',                'Piezas::index');
$routes->get('/piezas/create',         'Piezas::create');
$routes->post('/piezas/store',         'Piezas::store');
$routes->get('/piezas/(:num)/edit',    'Piezas::edit/$1');
$routes->post('/piezas/(:num)/update', 'Piezas::update/$1');
$routes->post('/piezas/(:num)/delete', 'Piezas::delete/$1');

// ── CRUD Compatibilidades ──────────────────────────────────────
$routes->get('/compatibilidades',                'Compatibilidades::index');
$routes->get('/compatibilidades/create',         'Compatibilidades::create');
$routes->post('/compatibilidades/store',         'Compatibilidades::store');
$routes->get('/compatibilidades/(:num)/edit',    'Compatibilidades::edit/$1');
$routes->post('/compatibilidades/(:num)/update', 'Compatibilidades::update/$1');
$routes->post('/compatibilidades/(:num)/delete', 'Compatibilidades::delete/$1');

// Confirmación desde Buscador (HTMX POST)
$routes->post('/compatibilidades/(:num)/confirm', 'Search::confirm/$1');

// ── Importador ─────────────────────────────────────────────────
$routes->get('/import',              'Import::index');
$routes->post('/import/upload',      'Import::upload');
$routes->get('/import/job/(:num)',   'Import::jobDetail/$1');
$routes->get('/import/pendientes',   'Import::pendientes');
$routes->post('/import/reenrich',         'Import::reenrich');
$routes->post('/import/detectar-modelos', 'Import::detectarModelos');

// API v1
$routes->group('api/v1', static function ($routes) {
    // Auth
    $routes->post('auth/login', 'Api\V1\AuthController::login');
    $routes->post('auth/refresh', 'Api\V1\AuthController::refresh');
    $routes->get('auth/me', 'Api\V1\AuthController::me');
    $routes->post('auth/logout', 'Api\V1\AuthController::logout');

    // Productos
    $routes->get('productos', 'Api\V1\ProductosController::index');
    $routes->get('productos/(:num)', 'Api\V1\ProductosController::show/$1');
    $routes->post('productos', 'Api\V1\ProductosController::create', ['filter' => 'role:admin']);
    $routes->put('productos/(:num)', 'Api\V1\ProductosController::update/$1', ['filter' => 'role:admin']);
    $routes->delete('productos/(:num)', 'Api\V1\ProductosController::delete/$1', ['filter' => 'role:admin']);

    // Busqueda
    $routes->get('search', 'Api\V1\SearchController::index');
    $routes->get('search-missed', 'Api\V1\SearchController::missed', ['filter' => 'role:admin']);

    // Compatibilidades
    $routes->get('compatibilidades', 'Api\V1\CompatibilidadesController::index');
    $routes->get('compatibilidades/(:num)', 'Api\V1\CompatibilidadesController::show/$1');
    $routes->post('compatibilidades', 'Api\V1\CompatibilidadesController::create', ['filter' => 'role:admin']);
    $routes->put('compatibilidades/(:num)', 'Api\V1\CompatibilidadesController::update/$1', ['filter' => 'role:admin']);
    $routes->delete('compatibilidades/(:num)', 'Api\V1\CompatibilidadesController::delete/$1', ['filter' => 'role:admin']);
    $routes->patch('compatibilidades/(:num)/confirmar', 'Api\V1\SearchController::confirmarCompatibilidad/$1', ['filter' => 'role:admin,vendedor']);

    // Motocicletas
    $routes->get('motocicletas', 'Api\V1\MotocicletasController::index');
    $routes->get('motocicletas/(:num)', 'Api\V1\MotocicletasController::show/$1');
    $routes->post('motocicletas', 'Api\V1\MotocicletasController::create', ['filter' => 'role:admin']);
    $routes->put('motocicletas/(:num)', 'Api\V1\MotocicletasController::update/$1', ['filter' => 'role:admin']);
    $routes->delete('motocicletas/(:num)', 'Api\V1\MotocicletasController::delete/$1', ['filter' => 'role:admin']);

    // Piezas
    $routes->get('piezas', 'Api\V1\PiezasController::index');
    $routes->get('piezas/(:num)', 'Api\V1\PiezasController::show/$1');
    $routes->post('piezas', 'Api\V1\PiezasController::create', ['filter' => 'role:admin']);
    $routes->put('piezas/(:num)', 'Api\V1\PiezasController::update/$1', ['filter' => 'role:admin']);
    $routes->delete('piezas/(:num)', 'Api\V1\PiezasController::delete/$1', ['filter' => 'role:admin']);

    // Aliases
    $routes->get('aliases', 'Api\V1\AliasesController::index');
    $routes->post('aliases', 'Api\V1\AliasesController::create', ['filter' => 'role:admin']);
    $routes->delete('aliases/(:num)', 'Api\V1\AliasesController::delete/$1', ['filter' => 'role:admin']);

    // Importador
    $routes->post('import/productos', 'Api\V1\ImportController::productos', ['filter' => 'role:admin']);
});
