<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Tests\EventListener;

use JulienDufresne\RequestId\RequestId;
use JulienDufresne\RequestIdBundle\EventListener\ResponseListener;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseListenerTest extends TestCase
{
    private $dispatcher;
    private $kernel;

    protected function setUp()
    {
        $requestIdService = new RequestIdService();
        $requestIdService->initRequestIdentifier(new RequestId('current', 'parent', 'root'));
        $this->dispatcher = new EventDispatcher();

        $listener = new ResponseListener($requestIdService, 'X-Current', 'X-Parent', 'X-Root');

        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse']);

        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->kernel = null;
    }

    public function testListener()
    {
        $response = new Response();
        $event = new FilterResponseEvent(
            $this->kernel,
            Request::create('/', 'GET', [], [], [], []),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $this->assertEquals('current', $response->headers->get('X-Current'));
        $this->assertEquals('root', $response->headers->get('X-Root'));
        $this->assertEquals('parent', $response->headers->get('X-Parent'));
    }
}
