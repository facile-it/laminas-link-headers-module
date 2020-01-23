<?php

namespace Facile\LaminasLinkHeadersModule;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ModuleTest extends TestCase
{
    public function testOnBootstrapAllEnabled(): void
    {
        $event = $this->prophesize(MvcEvent::class);
        $app = $this->prophesize(Application::class);
        $container = $this->prophesize(ContainerInterface::class);
        $eventManager = $this->prophesize(EventManagerInterface::class);
        $options = $this->prophesize(OptionsInterface::class);

        $linkHandler = $this->prophesize(Listener\LinkHandlerInterface::class);
        $stylesheetHandler = $this->prophesize(Listener\LinkHandlerInterface::class);
        $scriptHandler = $this->prophesize(Listener\LinkHandlerInterface::class);

        $event->getApplication()->willReturn($app->reveal());
        $app->getEventManager()->willReturn($eventManager->reveal());
        $app->getServiceManager()->willReturn($container->reveal());

        $container->get(OptionsInterface::class)->willReturn($options->reveal());
        $container->get(Listener\LinkHandler::class)->willReturn($linkHandler->reveal());
        $container->get(Listener\StylesheetHandler::class)->willReturn($stylesheetHandler->reveal());
        $container->get(Listener\ScriptHandler::class)->willReturn($scriptHandler->reveal());

        $eventManager->attach(MvcEvent::EVENT_FINISH, $linkHandler->reveal())
            ->shouldBeCalled();

        $options->isStylesheetEnabled()->willReturn(true);
        $options->isScriptEnabled()->willReturn(true);

        $eventManager->attach(MvcEvent::EVENT_FINISH, $stylesheetHandler->reveal())
            ->shouldBeCalled();

        $eventManager->attach(MvcEvent::EVENT_FINISH, $scriptHandler->reveal())
            ->shouldBeCalled();

        $module = new Module();
        $module->onBootstrap($event->reveal());
    }

    public function testOnBootstrapWithNoMvcEvent(): void
    {
        $event = $this->prophesize(EventInterface::class);

        $event->getName()->shouldNotBeCalled();

        $module = new Module();
        $module->onBootstrap($event->reveal());
    }

    public function testGetConfig(): void
    {
        $module = new Module();
        $result = $module->getConfig();

        $this->assertIsArray($result);
    }
}
