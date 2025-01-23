<?php

namespace Facile\LaminasLinkHeadersModule\Service;

use Facile\LaminasLinkHeadersModule\Options;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class OptionsConfigFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke(): void
    {
        $config = [
            'facile' => [
                'laminas_link_headers_module' => [
                    'stylesheet_enabled' => true,
                    'stylesheet_mode' => 'preload',
                    'http2_push_enabled' => false,
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')->willReturn($config);

        $factory = new OptionsConfigFactory();

        $result = $factory($container->reveal());

        $this->assertInstanceOf(Options::class, $result);
        $this->assertSame('preload', $result->getStylesheetMode());
        $this->assertSame(true, $result->isStylesheetEnabled());
        $this->assertSame(false, $result->isHttp2PushEnabled());
    }

    public function testInvokeWithDifferentParams(): void
    {
        $config = [
            'facile' => [
                'laminas_link_headers_module' => [
                    'stylesheet_enabled' => false,
                    'stylesheet_mode' => 'prefetch',
                    'http2_push_enabled' => true,
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')->willReturn($config);

        $factory = new OptionsConfigFactory();

        $result = $factory($container->reveal());

        $this->assertInstanceOf(Options::class, $result);
        $this->assertSame('prefetch', $result->getStylesheetMode());
        $this->assertSame(false, $result->isStylesheetEnabled());
        $this->assertSame(true, $result->isHttp2PushEnabled());
    }
}
