<?php

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/search', static function () {
    return service('response')
        ->setStatusCode(ResponseInterface::HTTP_NOT_IMPLEMENTED)
        ->setBody('<div class="alert alert-warning mb-0">Buscador en construccion: se habilita en el siguiente bloque.</div>');
});
