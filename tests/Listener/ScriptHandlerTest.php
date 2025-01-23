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
use Laminas\View\Helper\HeadScript;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

use function implode;

class ScriptHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testInvokeWithAnotherResponse(): void
    {
        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $response = $this->prophesize(Response::class);

        $headScript->getContainer()->shouldNotBeCalled();
        $response->getHeaders()->shouldNotBeCalled();
        $event->getResponse()->willReturn($response->reveal());
        $options->isScriptEnabled()->willReturn(true);

        $injector = new ScriptHandler($headScript->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithDisabled(): void
    {
        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $headScript->getContainer()->shouldNotBeCalled();
        $response->getHeaders()->shouldNotBeCalled();
        $event->getResponse()->willReturn($response->reveal());
        $options->isScriptEnabled()->willReturn(false);

        $injector = new ScriptHandler($headScript->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithExistingHeader(): void
    {
        $links = [
            $this->createStdClass([
                'attributes' => [
                    'src' => '/foo.js',
                ],
            ]),
            $this->createStdClass([
                'attributes' => [
                    'src' => '/bar.js',
                ],
            ]),
            $this->createStdClass([
                'content' => 'foo',
                'attributes' => [],
            ]),
        ];

        $expected = [
            '</existing-link>; as="style"',
            '</foo.js>; rel="preload"; as="script"; nopush',
            '</bar.js>; rel="preload"; as="script"; nopush',
        ];

        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);
        $existingHeader = $this->prophesize(HeaderInterface::class);

        $options->getScriptMode()->willReturn('preload');
        $options->isHttp2PushEnabled()->willReturn(false);
        $options->isScriptEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headScript->getContainer()->willReturn($headContainer->reveal());
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

        $injector = new ScriptHandler($headScript->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithNoPush(): void
    {
        $links = [
            $this->createStdClass([
                'attributes' => [
                    'src' => '/foo.js',
                ],
            ]),
            $this->createStdClass([
                'attributes' => [
                    'src' => '/bar.js',
                ],
            ]),
            $this->createStdClass([
                'content' => 'foo',
                'attributes' => [],
            ]),
        ];

        $expected = [
            '</foo.js>; rel="preload"; as="script"; nopush',
            '</bar.js>; rel="preload"; as="script"; nopush',
        ];

        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->getScriptMode()->willReturn('preload');
        $options->isHttp2PushEnabled()->willReturn(false);
        $options->isScriptEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headScript->getContainer()->willReturn($headContainer->reveal());
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

        $injector = new ScriptHandler($headScript->reveal(), $options->reveal());

        $injector($event->reveal());
    }

    public function testInvokeWithPushAndPrefetch(): void
    {
        $links = [
            $this->createStdClass([
                'attributes' => [
                    'src' => '/foo.js',
                ],
            ]),
            $this->createStdClass([
                'attributes' => [
                    'src' => '/bar.js',
                ],
            ]),
            $this->createStdClass([
                'content' => 'foo',
                'attributes' => [],
            ]),
        ];

        $expected = [
            '</foo.js>; rel="prefetch"; as="script"',
            '</bar.js>; rel="prefetch"; as="script"',
        ];

        $headScript = $this->prophesize(HeadScript::class);
        $options = $this->prophesize(OptionsInterface::class);
        $event = $this->prophesize(MvcEvent::class);
        $responseHeaders = $this->prophesize(Headers::class);
        $headContainer = $this->prophesize(AbstractContainer::class);
        $response = $this->prophesize(PhpEnvironment\Response::class);

        $options->getScriptMode()->willReturn('prefetch');
        $options->isHttp2PushEnabled()->willReturn(true);
        $options->isScriptEnabled()->willReturn(true);

        $headContainer->getIterator()->willReturn(new ArrayIterator($links));

        $headScript->getContainer()->willReturn($headContainer->reveal());
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

        $injector = new ScriptHandler($headScript->reveal(), $options->reveal());

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
