<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Tests\EventListener;

use JulienDufresne\RequestId\Factory\Generator\UniqueIdGeneratorInterface;
use JulienDufresne\RequestId\Factory\RequestIdFromRequestFactory;
use JulienDufresne\RequestIdBundle\EventListener\RequestListener;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestListenerTest extends TestCase
{
    private $dispatcher;
    private $kernel;
    private $requestIdService;

    protected function setUp()
    {
        $this->requestIdService = new RequestIdService();
        $this->dispatcher = new EventDispatcher();

        $generatorMock = $this->createMock(UniqueIdGeneratorInterface::class);
        $generatorMock->expects($this->any())
                      ->method('generateUniqueIdentifier')
                      ->willReturn('foo');

        $requestFactory = new RequestIdFromRequestFactory($generatorMock, 'X-root', 'X-parent');

        $listener = new RequestListener($this->requestIdService, $requestFactory);

        $this->dispatcher->addListener(KernelEvents::REQUEST, [$listener, 'onKernelRequest']);

        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->requestIdService = null;
        $this->kernel = null;
    }

    public function testListener()
    {
        $event = new GetResponseEvent(
            $this->kernel,
            Request::create('/', 'GET', [], [], [], ['HTTP_X-root' => 'bar', 'HTTP_X-parent' => 'baz']),
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

        $this->assertEquals('foo', $this->requestIdService->getCurrentAppRequestId());
        $this->assertEquals('bar', $this->requestIdService->getRootAppRequestId());
        $this->assertEquals('baz', $this->requestIdService->getParentAppRequestId());
    }

    public function testListenerDoNothingOnSubRequest()
    {
        $event = new GetResponseEvent(
            $this->kernel,
            Request::create('/', 'GET', [], [], [], ['HTTP_X-root' => 'bar', 'HTTP_X-parent' => 'baz']),
            HttpKernelInterface::SUB_REQUEST
        );

        $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

        $this->assertEquals('', $this->requestIdService->getCurrentAppRequestId());
        $this->assertEquals('', $this->requestIdService->getRootAppRequestId());
        $this->assertNull($this->requestIdService->getParentAppRequestId());
    }
}
