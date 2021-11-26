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

    /*
     |--------------------------------------------------------------------------
     | Autorun
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
    'global' => env('KITSUNE_CORE_GLOBAL', false),
];