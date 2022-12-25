<?php

use App\Controller\TestController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


$routes = new RouteCollection();

$routes->add('test1', new Route('/test1/{name}', [
    'name' => 'World',
    '_controller' => [new TestController, 'index']
]));

$routes->add('get_date', new Route('/date/{date?}', [
    'date' => null,
    '_controller' => TestController::class . '::date',
]));

$routes->add('hello', new Route('/hello', [
    '_controller' => TestController::class . '::hello',
]));

return $routes;