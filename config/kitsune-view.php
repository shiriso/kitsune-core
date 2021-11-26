<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Layout
     |--------------------------------------------------------------------------
     | Defines the default layout you want to use to customise your views.
     | This can be overridden on runtime using a Middleware or
     | call to Kitsune's setViewLayout method.
     |
     | For available layouts check the resources' directory
     | of the included package or your own derivatives.
     */
    'layout' => env('KITSUNE_VIEW_LAYOUT'),

    /*
     |--------------------------------------------------------------------------
     | Extra Paths
     |--------------------------------------------------------------------------
     | If you want Kitsune to include additional source directories which
     | are not already part of your paths configured at "view.paths"
     | you can define additional sources in here.
     |
     | Key: Used alias for your source
     | Source: Base-Path for your source
     | Paths: Default paths which will always be used
     |
     */
    'extra_sources' => [
        'published' => [
            'source' => resource_path('views/vendor'),
            'paths' => [],
        ],
        'vendor' => [
            'source' => base_path('vendor'),
            'paths' => [],
        ],
    ],
];