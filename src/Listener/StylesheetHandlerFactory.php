<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\View\Helper\HeadLink;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

final class StylesheetHandlerFactory
{
    public function __invoke(ContainerInterface $container): StylesheetHandler
    {
        /** @var HelperPluginManager $plugins */
        $plugins = $container->get('ViewHelperManager');
        /** @var HeadLink $headLink */
        $headLink = $plugins->get(HeadLink::class);

        $options = $container->get(OptionsInterface::class);

        return new StylesheetHandler($headLink, $options);
    }
}
