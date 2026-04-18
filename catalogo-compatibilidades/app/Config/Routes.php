<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

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
