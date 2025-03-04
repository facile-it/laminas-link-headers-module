<?php

namespace Facile\LaminasLinkHeadersModule\Listener;

use ArrayIterator;
use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Laminas\Http\Header\GenericMultiHeader;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Headers;
use Laminas\Http\PhpEnvironment;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

use function implode;

class LinkHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testInvokeWithAnotherResponse(): void
    {
        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $response = $this->prophesize(Response::class);

        $headLink->getContainer()->shouldNotBeCalled();
        $response->getHeaders()->shouldNotBeCalled();
        $event->getResponse()->willReturn($response->reveal());

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithNoItems(): void
    {
        $links = [];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->isHttp2PushEnabled()->willReturn(false);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->addHeader(Argument::any())
            ->shouldNotBeCalled();

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithAnotherExistingHeader(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PREFETCH,
                'href' => '/bar.css',
                'as' => 'style',
                'crossorigin' => 'same-origin',
                'type' => 'text/style',
                'media' => '(min-width: 100px)',
                'nopush' => 'nopush',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_DNS_PREFETCH,
                'href' => '/foo-dns-prefetch.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRECONNECT,
                'href' => '/foo-preconnect.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRERENDER,
                'href' => '/foo-prerender.css',
            ]),
        ];

        $expected = [
            '</existing-link>; as="style"',
            '</foo.css>; rel="preload"; nopush',
            '</bar.css>; rel="prefetch"; as="style"; crossorigin="same-origin"; type="text/style"; media="(min-width: 100px)"; nopush',
            '</foo-dns-prefetch.css>; rel="dns-prefetch"; nopush',
            '</foo-preconnect.css>; rel="preconnect"; nopush',
            '</foo-prerender.css>; rel="prerender"; nopush',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);
        $existingHeader = $this->prophesize(HeaderInterface::class);

        $options->isHttp2PushEnabled()->willReturn(false);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn($existingHeader->reveal());
        $responseHeaders->removeHeader($existingHeader->reveal())->shouldBeCalled();
        $existingHeader->getFieldValue()->willReturn('</existing-link>; as="style"');

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithAnotherExistingMultiHeader(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PREFETCH,
                'href' => '/bar.css',
                'as' => 'style',
                'crossorigin' => 'same-origin',
                'type' => 'text/style',
                'media' => '(min-width: 100px)',
                'nopush' => 'nopush',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_DNS_PREFETCH,
                'href' => '/foo-dns-prefetch.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRECONNECT,
                'href' => '/foo-preconnect.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRERENDER,
                'href' => '/foo-prerender.css',
            ]),
        ];

        $expected = [
            '</existing-link>; as="style"',
            '</foo.css>; rel="preload"; nopush',
            '</bar.css>; rel="prefetch"; as="style"; crossorigin="same-origin"; type="text/style"; media="(min-width: 100px)"; nopush',
            '</foo-dns-prefetch.css>; rel="dns-prefetch"; nopush',
            '</foo-preconnect.css>; rel="preconnect"; nopush',
            '</foo-prerender.css>; rel="prerender"; nopush',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);
        $existingHeader = $this->prophesize(HeaderInterface::class);

        $options->isHttp2PushEnabled()->willReturn(false);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn(new ArrayIterator([$existingHeader->reveal()]));
        $responseHeaders->removeHeader($existingHeader->reveal())->shouldBeCalled();
        $existingHeader->getFieldValue()->willReturn('</existing-link>; as="style"');

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithNoPush(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PREFETCH,
                'href' => '/bar.css',
                'as' => 'style',
                'crossorigin' => 'same-origin',
                'type' => 'text/style',
                'media' => '(min-width: 100px)',
                'nopush' => 'nopush',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_DNS_PREFETCH,
                'href' => '/foo-dns-prefetch.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRECONNECT,
                'href' => '/foo-preconnect.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRERENDER,
                'href' => '/foo-prerender.css',
            ]),
        ];

        $expected = [
            '</foo.css>; rel="preload"; nopush',
            '</bar.css>; rel="prefetch"; as="style"; crossorigin="same-origin"; type="text/style"; media="(min-width: 100px)"; nopush',
            '</foo-dns-prefetch.css>; rel="dns-prefetch"; nopush',
            '</foo-preconnect.css>; rel="preconnect"; nopush',
            '</foo-prerender.css>; rel="prerender"; nopush',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->isHttp2PushEnabled()->willReturn(false);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn(false);
        $responseHeaders->removeHeader(Argument::any())->shouldNotBeCalled();

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithPush(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
        ];

        $expected = [
            '</foo.css>; rel="preload"',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->isHttp2PushEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn(false);
        $responseHeaders->removeHeader(Argument::any())->shouldNotBeCalled();

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithNoRelAttribute(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
            $this->createStdClass([
                'href' => '/bar.css',
            ]),
        ];

        $expected = [
            '</foo.css>; rel="preload"',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->isHttp2PushEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn(false);
        $responseHeaders->removeHeader(Argument::any())->shouldNotBeCalled();

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithNoHrefAttribute(): void
    {
        $links = [
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
                'href' => '/foo.css',
            ]),
            $this->createStdClass([
                'rel' => OptionsInterface::MODE_PRELOAD,
            ]),
            $this->createStdClass([
                'rel' => 'stylesheet',
            ]),
        ];

        $expected = [
            '</foo.css>; rel="preload"',
        ];

        $headLink = $this->prophesize(HeadLink::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->isHttp2PushEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headLink->getContainer()->willReturn($headContainer->reveal());
        $response->getHeaders()->willReturn($responseHeaders->reveal());
        $event->getResponse()->willReturn($response->reveal());

        $responseHeaders->get('Link')->willReturn(false);
        $responseHeaders->removeHeader(Argument::any())->shouldNotBeCalled();

        $responseHeaders->addHeader(Argument::allOf(
            Argument::type(GenericMultiHeader::class),
            Argument::which('getFieldName', 'Link'),
            Argument::which('getFieldValue', implode(', ', $expected))
        ))
            ->shouldBeCalledTimes(1);

        $injector = new LinkHandler($headLink->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    private function createStdClass(array $properties): stdClass
    {
        $item = new stdClass();

        foreach ($properties as $name => $value) {
            $item->{$name} = $value;
        }

        return $item;
    }
}
