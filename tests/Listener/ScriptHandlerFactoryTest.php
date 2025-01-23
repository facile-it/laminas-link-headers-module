<?php

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\View\Helper\HeadScript;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ScriptHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $viewHelperManager = $this->prophesize(HelperPluginManager::class);
        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);

        $container->get(OptionsInterface::class)->willReturn($options->reveal());
        $container->get('ViewHelperManager')->willReturn($viewHelperManager->reveal());
        $viewHelperManager->get(HeadScript::class)->willReturn($headScript->reveal());

        $factory = new ScriptHandlerFactory();

        $result = $factory($container->reveal());

        $this->assertInstanceOf(ScriptHandler::class, $result);
    }
}
