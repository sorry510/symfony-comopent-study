<?php

namespace App\Simplex;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class Framework implements HttpKernelInterface
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected UrlMatcherInterface $urlMatcher,
        protected ControllerResolverInterface $controllerResolver,
        protected ArgumentResolverInterface $argumentResolver
    ){}

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    {
        try {
            $this->urlMatcher->getContext()->fromRequest($request);
            // dd($this->urlMatcher->match($request->getPathInfo()));

            $request->attributes->add($this->urlMatcher->match($request->getPathInfo()));

            $controller = $this->controllerResolver->getController($request);
            $arguments = $this->argumentResolver->getArguments($request, $controller);

            $response = call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $response = new Response('Not Found Router Match', 404);
        } catch (\Throwable $e) {
            $response = new Response('An error occurred', 500);
        }

        $this->dispatcher->dispatch(new ResponseEvent($response, $request), 'response');
        
        return $response;
    }
}