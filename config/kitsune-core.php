<?php

use Shiriso\Kitsune\Core\Kitsune;
use Shiriso\Kitsune\Core\KitsuneHelper;
use Shiriso\Kitsune\Core\KitsuneManager;
use Shiriso\Kitsune\Core\KitsunePriority;
use Shiriso\Kitsune\Core\SourceNamespace;
use Shiriso\Kitsune\Core\SourceRepository;

return [
    /*
     |--------------------------------------------------------------------------
     | Service
     |--------------------------------------------------------------------------
     | Defines various services which offer parts of our functionality.
     |
     | If you need to customise some kind of functionality according to your
     | application, you can extend the given classes or implement the
     | according interface and configure your class here.
     |
     */
    'service' => [
        /*
         |--------------------------------------------------------------------------
         | Class
         |--------------------------------------------------------------------------
         | Defines the Class which is used to provide Kitsune's core
         | functionalities like manipulating the view finder.
         |
         */
        'class' => Kitsune::class,

        /*
         |--------------------------------------------------------------------------
         | Helper
         |--------------------------------------------------------------------------
         | Defines the Class which is used to provide Helper-Methods,
         | which will be used to request specific information or
         | process various kinds of information like paths.
         |
         */
        'helper' => KitsuneHelper::class,

        /*
         |--------------------------------------------------------------------------
         | Manager
         |--------------------------------------------------------------------------
         | Defines the class which is used to manage various instances of
         | Kitsune's namespaced repositories, manages the currently
         | activated global mode namespace.
         |
         */
        'manager' => KitsuneManager::class,

        /*
         |--------------------------------------------------------------------------
         | Namespace
         |--------------------------------------------------------------------------
         | Defines the class reflecting a single namespace registered
         | to Kitsune and offers the functionality to manage
         | various sources inside the namespace.
         |
         */
        'namespace' => SourceNamespace::class,

        /*
         |--------------------------------------------------------------------------
         | Source
         |--------------------------------------------------------------------------
         | Defines the class reflecting a single namespace registered
         | to Kitsune and offers the functionality to manage
         | various sources inside the namespace.
         |
         */
        'source' => SourceRepository::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | Priorities
     |--------------------------------------------------------------------------
     | Priorities define the order of paths, in which they are added to
     | Laravel's view finder and thus will define the possible
     | overrides and which path will be considered first.
     |
     */
    'priority' => [
        /*
         |--------------------------------------------------------------------------
         | Definition
         |--------------------------------------------------------------------------
         | The definition is a class, which defines the possible cases to be used
         | and offers functionalities to for Kitsune to access the order value
         | which will be used to sort the paths.
         |
         | Hint: If you want to customise the available priorities and your
         | application is running on PHP 8.1 or later, you can also use
         | the new Enum feature and implement DefinesPriority there.
         |
         */
        'definition' => KitsunePriority::class,

        /*
         |--------------------------------------------------------------------------
         | Laravel
         |--------------------------------------------------------------------------
         | Laravel references the default paths which the ViewFinder is using
         | and will be used as reference for regular paths, if you want
         | to use the layout feature without loading extra sources.
         |
         | The affected paths are usually configured at "view.paths".
         |
         */
        'defaults' => [
            /*
             |--------------------------------------------------------------------------
             | Laravel
             |--------------------------------------------------------------------------
             | Laravel references the default paths which the ViewFinder is using
             | and will be used as reference for regular paths, if you want
             | to use the layout feature without loading extra sources.
             |
             | The affected paths are usually configured at "view.paths".
             |
             */
            'laravel' => 'high',

            /*
             |--------------------------------------------------------------------------
             | Namespace
             |--------------------------------------------------------------------------
             | Namespace references all additional namespaces which will be distributed
             | by kitsune, independent if they are added to the view configuration
             | or if they are added by another packages service provider.
             |
             | This is only the default, as the priority can be adjusted
             | to your needs from either of these sources.
             |
             | Note that different namespaces are never mixed and that the priority
             | will only affect the order of various sources in the namespace.
             |
             */
            'namespace' => 'medium',

            /*
             |--------------------------------------------------------------------------
             | Published
             |--------------------------------------------------------------------------
             | Published references a path which will automatically be added
             | when explicitly adding a package namespace to Kitsune.
             |
             | While this will use the already defined "published" extra source,
             | it will create new source using the given priority for cases in
             | which the extra source got removed from the configuration.
             |
             | Note that different namespaces are never mixed and that the priority
             | will only affect the order of various sources in the namespace.
             |
             */
            'published' => 'medium',

            /*
             |--------------------------------------------------------------------------
             | Extra Sources
             |--------------------------------------------------------------------------
             | Namespace references all additional namespaces which will be distributed
             | by kitsune, independent if they are added to the view configuration
             | or if they are added by another packages service provider.
             |
             | This is only the default, as the priority can be adjusted
             | to your needs from either of these sources.
             |
             | Note that different namespaces are never mixed and that the priority
             | will only affect the order of various sources in the namespace.
             |
             */
            'source' => 'low',
        ],
    ],

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
         | Global Mode Namespace
         |--------------------------------------------------------------------------
         | Defines which view namespace will be used for global mode if enabled.
         | Example: By default, everything which is registered for the kitsune
         | namespace would be published to the global scope if enabled.
         |
         */
        'namespace' => env('KITSUNE_CORE_GLOBAL_NAMESPACE', 'kitsune'),

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

    /*
     |--------------------------------------------------------------------------
     | Auto Refresh
     |--------------------------------------------------------------------------
     | If auto refresh is activated, Kitsune will update registered
     | namespaces and the possibly enabled global mode, whenever
     | a namespace or one of its sources gets updated.
     |
     */
    'auto_refresh' => env('KITSUNE_CORE_AUTO_REFRESH', true),
];