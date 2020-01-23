<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Service;

use Facile\LaminasLinkHeadersModule\Options;
use Psr\Container\ContainerInterface;

final class OptionsConfigFactory
{
    public function __invoke(ContainerInterface $container): Options
    {
        /** @var array $config */
        $config = $container->get('config');

        $configOptions = $config['facile']['laminas_link_headers_module'];

        return new Options($configOptions);
    }
}
