<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Buscador
$routes->get('/buscador', 'Search::index');
$routes->get('/search',   'Search::results');

// Confirmación de compatibilidad (HTMX POST)
$routes->post('/compatibilidades/(:num)/confirm', 'Search::confirm/$1');
