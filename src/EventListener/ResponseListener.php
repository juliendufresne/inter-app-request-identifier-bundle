<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\EventListener;

use JulienDufresne\RequestIdBundle\Service\RequestIdService;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

final class ResponseListener
{
    /** @var RequestIdService */
    private $requestIdService;
    /** @var string */
    private $currentResponseHeaderNames;
    /** @var string */
    private $parentResponseHeaderNames;
    /** @var string */
    private $rootResponseHeaderNames;

    public function __construct(
        RequestIdService $requestIdService,
        string $currentResponseHeaderNames,
        string $parentResponseHeaderNames,
        string $rootResponseHeaderNames
    ) {
        $this->requestIdService = $requestIdService;
        $this->currentResponseHeaderNames = $currentResponseHeaderNames;
        $this->parentResponseHeaderNames = $parentResponseHeaderNames;
        $this->rootResponseHeaderNames = $rootResponseHeaderNames;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $event->getResponse()->headers->add(
            array_filter(
                [
                    $this->currentResponseHeaderNames => $this->requestIdService->getCurrentAppRequestId(),
                    $this->parentResponseHeaderNames => $this->requestIdService->getParentAppRequestId(),
                    $this->rootResponseHeaderNames => $this->requestIdService->getRootAppRequestId(),
                ]
            )
        );
    }
}
