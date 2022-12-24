<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Simplex\Framework;
use App\Simplex\GoogleListener;
use Symfony\Component\Routing\Route;
use App\Simplex\ContentLengthListener;
use App\Simplex\StringResponseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;

$request = Request::createFromGlobals();
$requestStack = new RequestStack();

$routes = require __DIR__ . '/../app/routers/web.php';

$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();
$matcher = new UrlMatcher($routes, new RequestContext);
// $matcher = new CompiledUrlMatcher((new CompiledUrlMatcherDumper($routes))->getCompiledRoutes(), $context);

$dispatcher = new EventDispatcher();
// $dispatcher->addListener('response', [new GoogleListener, 'onResponse']);
// $dispatcher->addListener('response', [new ContentLengthListener, 'onResponse'], -255); // 数字越大，优先级越高，默认为0

// $dispatcher->addSubscriber(new ContentLengthListener());
// $dispatcher->addSubscriber(new GoogleListener());

$dispatcher->addSubscriber(new RouterListener($matcher, $requestStack));
$dispatcher->addSubscriber(new ErrorListener(function (FlattenException $exception) {
    $msg = 'Something went wrong! ('.$exception->getMessage().')';
    return new Response($msg, $exception->getStatusCode());
}));

$dispatcher->addSubscriber(new StringResponseListener());

// $listener = new ErrorListener(
//     'Calendar\Controller\ErrorController::exception' // 自定义类+方法
// );
// $dispatcher->addSubscriber($listener);

try {
    $app = new Framework($dispatcher, $controllerResolver, $requestStack, $argumentResolver);
    $app = new HttpCache(
        $app, 
        new Store(__DIR__ . '/../cache'),
        new Esi(),
        ['debug' => true]
    );
    $response = $app->handle($request);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found Router Match', 404);
} catch (\Throwable $e) {
    $response = new Response('An error occurred', 500);
}

$response->prepare($request)->send();

function render_template($request)
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include sprintf(__DIR__.'/../app/pages/%s.php', $_route);

    return new Response(ob_get_clean());
}