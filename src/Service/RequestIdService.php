<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Service;

use JulienDufresne\RequestId\RequestIdInterface;

final class RequestIdService implements RequestIdInterface
{
    /** @var RequestIdInterface|null */
    private $requestIdentifier;

    public function __construct(?RequestIdInterface $requestIdentifier = null)
    {
        $this->requestIdentifier = $requestIdentifier;
    }

    public function initRequestIdentifier(RequestIdInterface $requestIdentifier): void
    {
        if (null !== $this->requestIdentifier) {
            throw new \LogicException('Can not reset process identifier');
        }
        $this->requestIdentifier = $requestIdentifier;
    }

    /**
     * Uniquely identifies the root application execution id.
     *
     * @return string
     */
    public function getRootAppRequestId(): string
    {
        return null === $this->requestIdentifier ? '' : $this->requestIdentifier->getRootAppRequestId();
    }

    /**
     * Uniquely identifies the execution id of this application's caller.
     *
     * @return string|null
     */
    public function getParentAppRequestId(): ?string
    {
        return null === $this->requestIdentifier ? null : $this->requestIdentifier->getParentAppRequestId();
    }

    /**
     * Uniquely identifies the execution id of this application.
     *
     * @return string
     */
    public function getCurrentAppRequestId(): string
    {
        return null === $this->requestIdentifier ? '' : $this->requestIdentifier->getCurrentAppRequestId();
    }
}
