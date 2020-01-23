<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use function addslashes;
use function array_map;
use ArrayIterator;
use function count;
use function implode;
use function in_array;
use function iterator_to_array;
use Laminas\Http\Header\GenericMultiHeader;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\HeaderValue;
use Laminas\Http\PhpEnvironment\Response;
use function sprintf;
use function strtolower;

abstract class AbstractLinkHandler implements LinkHandlerInterface
{
    protected const RFC_PARAMS = [
        'rel',
        'anchor',
        'rev',
        'hreflang',
        'media',
        'title',
        'type',
    ];

    /**
     * Get the attribute string for the header
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    protected function getAttributeForHeader(string $name, $value): string
    {
        $name = strtolower($name);
        // all RFC params must have a value, but not link-extensions
        if (null === $value && ! in_array($name, self::RFC_PARAMS, true)) {
            return $name;
        }

        return sprintf('%s="%s"', $name, addslashes(HeaderValue::filter($value ?: null)));
    }

    /**
     * Returns the link header
     *
     * @param array $attributes
     *
     * @return string
     */
    protected function createLinkHeaderValue(array $attributes): string
    {
        $href = $attributes['href'];
        unset($attributes['href']);

        $attributeLines = [];
        foreach ($attributes as $name => $value) {
            $attributeLines[] = $this->getAttributeForHeader($name, $value);
        }

        return sprintf('<%s>; %s', $href, implode('; ', $attributeLines));
    }

    /**
     * @param Response $response
     * @param string[] $values
     */
    protected function addLinkHeader(Response $response, array $values): void
    {
        if (! count($values)) {
            return;
        }

        $header = new GenericMultiHeader('Link', implode(', ', $values));
        $currentHeader = $response->getHeaders()->get($header->getFieldName());

        /** @var HeaderInterface[] $headers */
        $headers = [];
        if ($currentHeader instanceof ArrayIterator) {
            $headers = iterator_to_array($currentHeader);
        } elseif (false !== $currentHeader) {
            $headers[] = $currentHeader;
        }

        foreach ($headers as $headerItem) {
            $response->getHeaders()->removeHeader($headerItem);
        }

        $headers[] = $header;

        $headerValues = array_map(
            static function (HeaderInterface $header) {
                return $header->getFieldValue();
            },
            $headers
        );

        $response->getHeaders()->addHeader(new GenericMultiHeader($header->getFieldName(), implode(', ', $headerValues)));
    }
}
