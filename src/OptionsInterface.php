<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule;

interface OptionsInterface
{
    public const MODE_PRELOAD = 'preload';

    public const MODE_PREFETCH = 'prefetch';

    public const MODE_DNS_PREFETCH = 'dns-prefetch';

    public const MODE_PRECONNECT = 'preconnect';

    public const MODE_PRERENDER = 'prerender';

    public function isStylesheetEnabled(): bool;

    public function getStylesheetMode(): string;

    public function isScriptEnabled(): bool;

    public function getScriptMode(): string;

    public function isHttp2PushEnabled(): bool;
}
