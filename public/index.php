<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;

$request = Request::createFromGlobals();
$response = new Response();

$routes = require __DIR__ . '/../app/routers/web.php';

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);
// $matcher = new CompiledUrlMatcher((new CompiledUrlMatcherDumper($routes))->getCompiledRoutes(), $context);

try {
    $match = $matcher->match($request->getPathInfo());
    $request->attributes->add($match);
    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
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