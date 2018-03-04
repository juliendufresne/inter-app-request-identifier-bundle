<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\EventListener;

use JulienDufresne\RequestId\Factory\RequestIdFromConsoleFactory;
use JulienDufresne\RequestIdBundle\Service\RequestIdService;

final class ConsoleListener
{
    /** @var RequestIdFromConsoleFactory */
    private $consoleFactory;
    /** @var RequestIdService */
    private $requestIdService;

    public function __construct(RequestIdService $requestIdService, RequestIdFromConsoleFactory $consoleFactory)
    {
        $this->requestIdService = $requestIdService;
        $this->consoleFactory = $consoleFactory;
    }

    public function onConsoleCommand(): void
    {
        $this->requestIdService->initRequestIdentifier($this->consoleFactory->create());
    }
}
