<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Tests\Service;

use JulienDufresne\RequestId\RequestIdInterface;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JulienDufresne\RequestIdBundle\Service\RequestIdService
 */
final class RequestIdServiceTest extends TestCase
{
    /** @var RequestIdInterface|MockObject */
    private $requestIdentifierMock;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        $this->requestIdentifierMock = $this->createMock(RequestIdInterface::class);
        $this->requestIdentifierMock->expects(self::any())
                                    ->method('getRootAppRequestId')
                                    ->willReturn('A');
        $this->requestIdentifierMock->expects(self::any())
                                    ->method('getParentAppRequestId')
                                    ->willReturn('B');
        $this->requestIdentifierMock->expects(self::any())
                                    ->method('getCurrentAppRequestId')
                                    ->willReturn('C');
    }

    public function testCreateEmpty()
    {
        $object = new RequestIdService();

        $this->assertEquals('', $object->getRootAppRequestId());
        $this->assertNull($object->getParentAppRequestId());
        $this->assertEquals('', $object->getCurrentAppRequestId());
    }

    public function testCreateWithRequestIdentifier()
    {
        $object = new RequestIdService($this->requestIdentifierMock);

        $this->assertEquals('A', $object->getRootAppRequestId());
        $this->assertEquals('B', $object->getParentAppRequestId());
        $this->assertEquals('C', $object->getCurrentAppRequestId());
    }

    public function testInitProcessIdentifier()
    {
        $object = new RequestIdService();

        $requestIdentifierMock = $this->createMock(RequestIdInterface::class);

        $object->initRequestIdentifier($requestIdentifierMock);

        $this->expectException(\LogicException::class);
        $object->initRequestIdentifier($requestIdentifierMock);
    }
}
