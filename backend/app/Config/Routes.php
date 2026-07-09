<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', '\Sites\Endpoint\Controllers\ApiDocsController::index');

$routes->options('(:any)', '', ['filter' => 'cors']);

require ROOTPATH . 'Sites/Endpoint/Config/Routes.php';
