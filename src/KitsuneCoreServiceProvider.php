<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneHelper;
use Shiriso\Kitsune\Core\Events\KitsuneNamespaceUpdated;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneHelperException;
use Shiriso\Kitsune\Core\Listeners\UpdateKitsuneForNamespace;
use Shiriso\Kitsune\Core\Middleware\KitsuneGlobalModeMiddleware;
use Shiriso\Kitsune\Core\Middleware\KitsuneMiddleware;

class KitsuneCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
        }

        $this->addEventListeners();

        app('kitsune')->start();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kitsune-core.php', 'kitsune.core');
        $this->mergeConfigFrom(__DIR__.'/../config/kitsune-view.php', 'kitsune.view');

        $this->registerHelpers();
        $this->registerKitsune();
        $this->registerMiddleware();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['kitsune', 'kitsune.helper', 'kitsune.manager'];
    }

    /**
     * Register Kitsune's Services to the application.
     *
     * @return void
     * @throws InvalidKitsuneHelperException
     */
    protected function registerKitsune()
    {
        if (!is_a(
            $helperClass = config('kitsune.core.service.helper', Kitsune::class),
            IsKitsuneHelper::class,
            true
        )) {
            throw new InvalidKitsuneHelperException($helperClass);
        }

        $this->app->singleton('kitsune.helper', $helperClass);
        $this->app->singleton('kitsune', app('kitsune.helper')->getCoreClass());
        $this->app->singleton('kitsune.manager', app('kitsune.helper')->getManagerClass());
    }

    /**
     * Register Kitsune's middlewares with according shortnames.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        app('router')
            ->aliasMiddleware('kitsune', KitsuneMiddleware::class)
            ->aliasMiddleware('kitsune.global', KitsuneGlobalModeMiddleware::class);
    }

    /**
     * Register additional Helpers Kitsune is using.
     *
     * @return void
     */
    protected function registerHelpers()
    {
        Arr::macro('mapWithKeys', function (array $array, callable $callable) {
            return array_map_with_keys($callable, $array);
        });
    }

    /**
     * Publish the config files.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/kitsune-core.php' => config_path('kitsune/core.php'),
            __DIR__.'/../config/kitsune-view.php' => config_path('kitsune/view.php'),
        ], 'kitsune.config');
    }

    protected function addEventListeners()
    {
        Event::listen(KitsuneNamespaceUpdated::class, UpdateKitsuneForNamespace::class);
    }
}