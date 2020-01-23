<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule;

use Laminas\Stdlib\AbstractOptions;

final class Options extends AbstractOptions implements OptionsInterface
{
    /**
     * @var bool
     */
    private $stylesheetEnabled = false;

    /**
     * @var string
     */
    private $stylesheetMode = self::MODE_PRELOAD;

    /**
     * @var bool
     */
    private $scriptEnabled = false;

    /**
     * @var string
     */
    private $scriptMode = self::MODE_PRELOAD;

    /**
     * @var bool
     */
    private $http2PushEnabled = false;

    public function isStylesheetEnabled(): bool
    {
        return $this->stylesheetEnabled;
    }

    public function setStylesheetEnabled(bool $stylesheetEnabled): void
    {
        $this->stylesheetEnabled = $stylesheetEnabled;
    }

    public function getStylesheetMode(): string
    {
        return $this->stylesheetMode;
    }

    public function setStylesheetMode(string $stylesheetMode): void
    {
        $this->stylesheetMode = $stylesheetMode;
    }

    public function isHttp2PushEnabled(): bool
    {
        return $this->http2PushEnabled;
    }

    public function setHttp2PushEnabled(bool $http2PushEnabled): void
    {
        $this->http2PushEnabled = $http2PushEnabled;
    }

    public function isScriptEnabled(): bool
    {
        return $this->scriptEnabled;
    }

    public function setScriptEnabled(bool $scriptEnabled): void
    {
        $this->scriptEnabled = $scriptEnabled;
    }

    public function getScriptMode(): string
    {
        return $this->scriptMode;
    }

    public function setScriptMode(string $scriptMode): void
    {
        $this->scriptMode = $scriptMode;
    }
}
