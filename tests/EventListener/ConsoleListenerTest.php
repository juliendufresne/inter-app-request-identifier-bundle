<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Tests\EventListener;

use JulienDufresne\RequestId\Factory\Generator\UniqueIdGeneratorInterface;
use JulienDufresne\RequestId\Factory\RequestIdFromConsoleFactory;
use JulienDufresne\RequestIdBundle\EventListener\ConsoleListener;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ConsoleListenerTest extends TestCase
{
    public function testListener()
    {
        $service = new RequestIdService();
        $generatorMock = $this->createMock(UniqueIdGeneratorInterface::class);
        $generatorMock->expects($this->once())
            ->method('generateUniqueIdentifier')
            ->willReturn('foo');

        $consoleFactory = new RequestIdFromConsoleFactory($generatorMock);

        $listener = new ConsoleListener($service, $consoleFactory);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::COMMAND, [$listener, 'onConsoleCommand']);

        $this->assertEmpty($service->getCurrentAppRequestId());

        $dispatcher->dispatch(ConsoleEvents::COMMAND);

        $this->assertEquals('foo', $service->getCurrentAppRequestId());
        $this->assertEquals($service->getRootAppRequestId(), $service->getCurrentAppRequestId());
    }
}
