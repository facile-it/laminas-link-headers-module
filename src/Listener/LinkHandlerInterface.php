<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use Laminas\Mvc\MvcEvent;

interface LinkHandlerInterface
{
    public function __invoke(MvcEvent $event): void;
}
