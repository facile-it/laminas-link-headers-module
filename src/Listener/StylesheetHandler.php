<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use function array_filter;
use Facile\LaminasLinkHeadersModule\OptionsInterface;
use function get_object_vars;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\HeadLink;

final class StylesheetHandler extends AbstractLinkHandler
{
    private const TYPE_STYLESHEET = 'stylesheet';

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
        if (! $this->options->isStylesheetEnabled()) {
            return;
        }

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

            $attributes = [
                'href' => $attributes['href'],
                'rel' => $this->options->getStylesheetMode(),
                'as' => 'script',
                'type' => $attributes['type'] ?? null,
                'media' => $attributes['media'] ?? null,
            ];

            $attributes = array_filter($attributes);

            $attributes['rel'] = $this->options->getStylesheetMode();
            $attributes['as'] = 'style';

            if (! $this->options->isHttp2PushEnabled()) {
                $attributes['nopush'] = null;
            }

            $values[] = $this->createLinkHeaderValue($attributes);
        }

        $this->addLinkHeader($response, $values);
    }

    /**
     * Whether the link is valid to be injected in headers
     *
     * @param array $attributes
     *
     * @return bool
     */
    private function canInjectLink(array $attributes): bool
    {
        if (empty($attributes['href'] ?? '')) {
            return false;
        }

        return self::TYPE_STYLESHEET === ($attributes['rel'] ?? '');
    }
}
