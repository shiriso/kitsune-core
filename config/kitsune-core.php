<?php

use Shiriso\Kitsune\Core\Kitsune;

return [
    /*
     |--------------------------------------------------------------------------
     | Helper
     |--------------------------------------------------------------------------
     | Defines the Class which is used to provide Helper-Methods,
     | which will be used to request specific information.
     |
     | Class can be replaced for cases where it makes sense to replace specific
     | methods based on the current application, for example if
     | branding/theming-Support is given.
     |
     */
    'helper' => Kitsune::class,

    'global_mode' => [
        /*
         |--------------------------------------------------------------------------
         | Global Mode Enabled
         |--------------------------------------------------------------------------
         | Defines if Kitsune will customise the view paths on a global scope.
         |
         | If you want to apply the override possibility for every view name
         | to be resolved on a global scope without a specific prefix.
         |
         | This means that you do not need to adjust any paths inside your
         | application and overrides will be applied whenever possible.
         |
         | Namespaced access will always be available, if you only want Kitsune
         | to be used for specific templates. Simply prefix your view's name
         | by "kitsune::" and overrides will be enabled for that specific view.
         |
         */
        'enabled' => env('KITSUNE_CORE_GLOBAL_MODE', false),

        /*
         |--------------------------------------------------------------------------
         | Reset on Disable Global Mode
         |--------------------------------------------------------------------------
         | Defines if Kitsune will reset the global source paths if global mode
         | gets disabled during runtime.
         |
         | This may be necessary if you are running global mode as default,
         | but want to disable it using the middleware for specific routes
         | for example, as they Kitsune would have already added the
         | sources during the boot process of the application.
         |
         | Keep in mind though, that we can only reset the paths to its
         | default retrieved from the configuration and possible
         | runtime adjustments to it may not be respected.
         |
         */
        'reset_on_disable' => env('KITSUNE_CORE_GLOBAL_MODE_RESET', true),
    ],
];