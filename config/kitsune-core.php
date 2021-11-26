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
     | Defines if Kitsune will customise the view paths automatically
     | when booting the application.
     |
     | If you want to enable it for your entire site or want it enabled
     | as default with the option to override it for specific routes,
     | set the autorun option to true.
     |
     | This behaviour can be disabled, if you only want customised views
     | for specific routes / parts of your website. You could use the
     | KitsuneMiddleware for example, to set a different layout only
     | only for your administration-area without affecting your
     | usual publicly available site.
     |
     */
    'autorun' => env('KITSUNE_CORE_AUTORUN', true),
];