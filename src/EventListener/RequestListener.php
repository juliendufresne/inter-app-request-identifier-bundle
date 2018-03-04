<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\EventListener;

use JulienDufresne\RequestId\Factory\RequestIdFromRequestFactory;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class RequestListener
{
    /** @var RequestIdService */
    private $requestIdService;
    /** @var RequestIdFromRequestFactory */
    private $requestFactory;

    public function __construct(RequestIdService $requestIdService, RequestIdFromRequestFactory $requestFactory)
    {
        $this->requestIdService = $requestIdService;
        $this->requestFactory = $requestFactory;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->requestIdService->initRequestIdentifier($this->requestFactory->create($request->headers->all()));
    }
}
