<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Listener;

use Facile\LaminasLinkHeadersModule\OptionsInterface;
use Laminas\View\Helper\HeadScript;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

class ScriptHandlerFactory
{
    public function __invoke(ContainerInterface $container): ScriptHandler
    {
        /** @var HelperPluginManager $plugins */
        $plugins = $container->get('ViewHelperManager');
        /** @var HeadScript $headScript */
        $headScript = $plugins->get(HeadScript::class);

        $options = $container->get(OptionsInterface::class);

        return new ScriptHandler($headScript, $options);
    }
}
