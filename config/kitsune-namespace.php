<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Namespace
     |--------------------------------------------------------------------------
     | Defines the configuration for the package's namespace.
     |
     | The name of the namespace will be retrieved on your filename if
     | the configuration is published, or based on the key you will
     | merge it into in the packages service provider.
     |
     | Available Settings:
     | "layout": Define a layout to be used only within the namespace.
     | "paths": Defines additional paths which will be
     |                 added to the namespace.
     | "add_defaults": Usually Laravel's source will not be added,
     |                 but if you desire to do so, you can
     |                 achieve this using this flag.
     | "priority": A higher priority will result in views taking
     |             precedence over those in a lower priority.
     |
     */
    'namespace' => [
        'add_defaults' => true,
    ],
    /*
     |--------------------------------------------------------------------------
     | Sources
     |--------------------------------------------------------------------------
     | If you want to add non default sources to your namespace for
     | enhanced sorting possibilities or different base paths.
     |
     | The "published" and the "vendor" will always be available
     | by default and do not need to be specified.
     |
     | Key: Used alias for your source.
     | "base_path": Base-Path for your source.
     | "paths": Default paths which will always be used.
     | "priority": A higher priority will result in views taking
     |             precedence over those in a lower priority.
     |
     */
    'sources' => [],
    /*
     |--------------------------------------------------------------------------
     | Paths
     |--------------------------------------------------------------------------
     | Defines the paths to be added for the specific sources.
     |
     | Keep in mind, that the name of your package
     | will be added to published automatically.
     |
     | If the package is named "kitsune", it will add "kitsune" to published.
     |
     | Key: Used alias for your source.
     | Value: Array of paths or path as string.
     |
     */
    'paths' => [
        'vendor' => [],
        'published' => [],
    ],
];
