<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\HeadLink;

use function get_object_vars;
use function in_array;

final class LinkHandler extends AbstractLinkHandler
{
    private const ALLOWED_RELS = [
        OptionsInterface::MODE_PRELOAD,
        OptionsInterface::MODE_PREFETCH,
        OptionsInterface::MODE_DNS_PREFETCH,
        OptionsInterface::MODE_PRECONNECT,
        OptionsInterface::MODE_PRERENDER,
    ];

    /**
     * @var HeadLink
     */
    private $headLink;

    /**
     * @var OptionsInterface
     */
    private $options;

    public function __construct(HeadLink $headLink, OptionsInterface $options)
    {
        $this->headLink = $headLink;
        $this->options = $options;
    }

    public function __invoke(MvcEvent $event): void
    {
        $response = $event->getResponse();
        if (! $response instanceof Response) {
            return;
        }

        $values = [];

        foreach ($this->headLink->getContainer() as $item) {
            $attributes = get_object_vars($item);

            if (! $this->canInjectLink($attributes)) {
                continue;
            }

            if (! $this->options->isHttp2PushEnabled()) {
                $attributes['nopush'] = null;
            }

            $values[] = $this->createLinkHeaderValue($attributes);
        }

        $this->addLinkHeader($response, $values);
    }

    /**
     * Whether the link is valid to be injected in headers.
     */
    private function canInjectLink(array $attributes): bool
    {
        if (empty($attributes['href'] ?? '')) {
            return false;
        }

        return in_array($attributes['rel'] ?? '', self::ALLOWED_RELS, true);
    }
}
