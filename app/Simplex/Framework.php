<?php

namespace App\Simplex;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class Framework extends HttpKernel implements HttpKernelInterface
{
    public function __construct($routes)
    {
        $context = new RequestContext();
        $matcher = new UrlMatcher($routes, $context);
        $requestStack = new RequestStack();

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, $requestStack));
        $dispatcher->addSubscriber(new ResponseListener('UTF-8'));
        $dispatcher->addSubscriber(new StringResponseListener());

        parent::__construct($dispatcher, $controllerResolver, $requestStack, $argumentResolver);
    }

    // public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    // {
    //     try {
    //         $this->urlMatcher->getContext()->fromRequest($request);
    //         // dd($this->urlMatcher->match($request->getPathInfo()));

    //         $request->attributes->add($this->urlMatcher->match($request->getPathInfo()));

    //         $controller = $this->controllerResolver->getController($request);
    //         $arguments = $this->argumentResolver->getArguments($request, $controller);

    //         $response = call_user_func_array($controller, $arguments);
    //     } catch (ResourceNotFoundException $exception) {
    //         $response = new Response('Not Found Router Match', 404);
    //     } catch (\Throwable $e) {
    //         $response = new Response('An error occurred', 500);
    //     }

    //     $this->dispatcher->dispatch(new ResponseEvent($response, $request), 'response');
        
    //     return $response;
    // }
}