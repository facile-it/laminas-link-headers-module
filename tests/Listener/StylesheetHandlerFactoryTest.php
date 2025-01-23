<?php

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class StylesheetHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $viewHelperManager = $this->prophesize(HelperPluginManager::class);
        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);

        $container->get(OptionsInterface::class)->willReturn($options->reveal());
        $container->get('ViewHelperManager')->willReturn($viewHelperManager->reveal());
        $viewHelperManager->get(HeadLink::class)->willReturn($headLink->reveal());

        $factory = new StylesheetHandlerFactory();

        $result = $factory($container->reveal());

        $this->assertInstanceOf(StylesheetHandler::class, $result);
    }
}
