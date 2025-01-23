<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\HeadScript;

use function array_filter;
use function get_object_vars;

final class ScriptHandler extends AbstractLinkHandler
{
    /**
     * @var HeadScript
     */
    private $headScript;

    /**
     * @var OptionsInterface
     */
    private $options;

    public function __construct(HeadScript $headScript, OptionsInterface $options)
    {
        $this->headScript = $headScript;
        $this->options = $options;
    }

    public function __invoke(MvcEvent $event): void
    {
        if (! $this->options->isScriptEnabled()) {
            return;
        }

        $response = $event->getResponse();
        if (! $response instanceof Response) {
            return;
        }

        $values = [];

        foreach ($this->headScript->getContainer() as $item) {
            $properties = get_object_vars($item);
            $attributes = $properties['attributes'] ?? [];

            if (! $this->canInjectLink($attributes)) {
                continue;
            }

            $attributes = [
                'href' => $attributes['src'],
                'rel' => $this->options->getScriptMode(),
                'as' => 'script',
                'type' => $properties['type'] ?? null,
            ];

            $attributes = array_filter($attributes);

            if (! $this->options->isHttp2PushEnabled()) {
                $attributes['nopush'] = null;
            }

            $values[] = $this->createLinkHeaderValue($attributes);
        }

        $this->addLinkHeader($response, $values);
    }

    /**
     * Whether the link is valid to be injected in headers.
     *
     * @param array<string, mixed> $attributes
     */
    private function canInjectLink(array $attributes): bool
    {
        return ! empty($attributes['src'] ?? '');
    }
}
