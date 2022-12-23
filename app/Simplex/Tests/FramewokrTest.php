<?php

namespace App\Simplex\Tests;

use App\Simplex\Framework;
use PHPUnit\Framework\TestCase;
use App\Controller\TestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class FrameworkTest extends TestCase
{
    public function testNotFoundHanding()
    {
        $framework = $this->getFrameworkForException(new ResourceNotFoundException);

        $response = $framework->handle(new Request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testErrorHandling()
    {
        $framework = $this->getFrameworkForException(new \RuntimeException());

        $response = $framework->handle(new Request());

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testControllerResponse()
    {
        $matcher = $this->createMock(UrlMatcherInterface::class);

        $matcher->expects($this->once())
            ->method('match')
            ->will($this->returnValue([
                "date" => "2022-12-12",
                "_controller" => TestController::class . "::date",
                "_route" => "get_date"
            ]));

        $matcher->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->createMock(RequestContext::class)));
            
        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        $framework = new Framework($matcher, $controllerResolver, $argumentResolver);
        $response = $framework->handle(new Request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('2022-12-12', $response->getContent());
    }

    private function getFrameworkForException($exception)
    {
        $matcher = $this->createMock(UrlMatcherInterface::class);

        $matcher->expects($this->once())
            ->method('match')
            ->will($this->throwException($exception));
        
        $matcher->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->createMock(RequestContext::class)));

        $controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $argumentResolver = $this->createMock(ArgumentResolverInterface::class);

        return new Framework($matcher, $controllerResolver, $argumentResolver);
    }
}
