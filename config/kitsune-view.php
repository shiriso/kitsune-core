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
     |
     */
    'layout' => env('KITSUNE_VIEW_LAYOUT'),

    /*
     |--------------------------------------------------------------------------
     | Namespaces
     |--------------------------------------------------------------------------
     | Defines the namespaces which will be available by default.
     |
     | Structure is either the name of the namespace as value or as key
     | with an associative array of values to configure the namespace
     | accordingly to your desires.
     |
     | Available Settings:
     | "layout": Define a layout to be used only within the namespace.
     | "paths": Defines additional paths which will be
     |                 added to the namespace.
     | "include_defaults": Usually Laravel's source will not be included,
     |                 but if you desire to do so, you can
     |                 achieve this using this flag.
     | "priority": A higher priority will result in views taking
     |             precedence over those in a lower priority.
     |
     */
    'namespaces' => [],

    /*
     |--------------------------------------------------------------------------
     | Sources
     |--------------------------------------------------------------------------
     | If you want Kitsune to automatically add sources to your namespace,
     | which are not part of the applications defaults view source paths
     | configured at the 'paths' key inside your config/view.php.
     |
     | Key: Used alias for your source.
     | "base_path": Base-Path for your source.
     | "paths": Default paths which will always be used.
     | "priority": A higher priority will result in views taking
     |             precedence over those in a lower priority.
     |
     */
    'sources' => [],
];
