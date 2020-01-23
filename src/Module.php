<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule;

use Facile\LaminasLinkHeadersModule\Listener\LinkHandler;
use Facile\LaminasLinkHeadersModule\Listener\ScriptHandler;
use Facile\LaminasLinkHeadersModule\Listener\StylesheetHandler;
use Laminas\EventManager\EventInterface;
use Laminas\Mvc\MvcEvent;

final class Module
{
    public function onBootstrap(EventInterface $e): void
    {
        if (! $e instanceof MvcEvent) {
            return;
        }

        $app = $e->getApplication();
        $container = $app->getServiceManager();
        $eventManager = $app->getEventManager();

        $linkHandler = $container->get(LinkHandler::class);
        $eventManager->attach(MvcEvent::EVENT_FINISH, $linkHandler);

        $stylesheetHandler = $container->get(StylesheetHandler::class);
        $eventManager->attach(MvcEvent::EVENT_FINISH, $stylesheetHandler);

        $scriptHandler = $container->get(ScriptHandler::class);
        $eventManager->attach(MvcEvent::EVENT_FINISH, $scriptHandler);
    }

    public function getConfig(): array
    {
        return [
            'facile' => [
                'laminas_link_headers_module' => [
                    'stylesheet_enabled' => false,
                    'stylesheet_mode' => 'preload',
                    'script_enabled' => false,
                    'script_mode' => 'preload',
                    'http2_push_enabled' => true,
                ],
            ],
            'service_manager' => [
                'factories' => [
                    OptionsInterface::class => Service\OptionsConfigFactory::class,
                    Listener\LinkHandler::class => Listener\LinkHandlerFactory::class,
                    Listener\StylesheetHandler::class => Listener\StylesheetHandlerFactory::class,
                    Listener\ScriptHandler::class => Listener\ScriptHandlerFactory::class,
                ],
            ],
        ];
    }
}
