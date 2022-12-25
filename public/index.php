<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Simplex\Framework;
use App\Simplex\StringResponseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$request = Request::createFromGlobals();

try {
    $container = require __DIR__ . '/../app/Simplex/Container.php';
    
    $container->register('listener.response.string', StringResponseListener::class);
    $container->getDefinition('dispatcher')
        ->addMethodCall('addSubscriber', [new Reference('listener.response.string')]);

    $app = $container->get('framework');
    $app = new HttpCache(
        $app, 
        new Store(__DIR__ . '/../cache'),
        new Esi(),
        ['debug' => false]
    );
    $response = $app->handle($request);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found Router Match', 404);
} catch (\Throwable $e) {
    $response = new Response($e->getMessage(), 500);
}

$response->prepare($request)->send();

function render_template($request)
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include sprintf(__DIR__.'/../app/pages/%s.php', $_route);

    return new Response(ob_get_clean());
}