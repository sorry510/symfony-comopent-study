<?php

use App\Controller\TestController;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;


$routes = new RouteCollection();
$routes->add('test1', new Route('/test1/{name}', [
    'name' => 'World',
    '_controller' => [new TestController, 'index']
]));

return $routes;