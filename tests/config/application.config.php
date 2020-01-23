<?php
/**
 * Configuration file generated by ZFTool
 * The previous configuration file is stored in application.config.old.
 *
 * @see https://github.com/zendframework/ZFTool
 */
return [
    'modules' => [
        'Laminas\Router',
        'Facile\LaminasLinkHeadersModule',
    ],
    'module_listener_options' => [
        'config_cache_enabled' => false,
        'module_map_cache_enabled' => false,
        'module_paths' => [
            './../src',
            './vendor',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}global.php',
        ],
    ],
];
