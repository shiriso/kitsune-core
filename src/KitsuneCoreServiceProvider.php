<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneHelper;
use Shiriso\Kitsune\Core\Events\KitsuneCoreInitialized;
use Shiriso\Kitsune\Core\Events\KitsuneCoreUpdated;
use Shiriso\Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Shiriso\Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneHelperException;
use Shiriso\Kitsune\Core\Listeners\CoreInitialized;
use Shiriso\Kitsune\Core\Listeners\HandleCoreUpdate;
use Shiriso\Kitsune\Core\Listeners\PropagateSourceUpdate;
use Shiriso\Kitsune\Core\Listeners\UpdateKitsuneForNamespace;
use Shiriso\Kitsune\Core\Middleware\KitsuneGlobalModeMiddleware;
use Shiriso\Kitsune\Core\Middleware\KitsuneLayoutMiddleware;
use Shiriso\Kitsune\Core\Middleware\KitsuneMiddleware;

class KitsuneCoreServiceProvider extends ServiceProvider
{
    use UtilisesKitsune;

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

        $this->getKitsuneCore()->initialize();
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
        $this->mergeConfigFrom(__DIR__.'/../config/kitsune-namespace.php', 'kitsune.packages.kitsune');

        $this->registerHelpers();
        $this->registerKitsuneServices();
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
    protected function registerKitsuneServices()
    {
        $this->app->singleton('kitsune.helper', $this->getHelperClass());
        $this->app->singleton('kitsune', app('kitsune.helper')->getCoreClass());
        $this->app->singleton('kitsune.manager', app('kitsune.helper')->getManagerClass());
    }

    /**
     * Retrieve the service class currently representing the Kitsune helper.
     *
     * @return string
     * @throws InvalidKitsuneHelperException
     */
    protected function getHelperClass(): string
    {
        if (is_a(
            $helperClass = config('kitsune.core.service.helper', Kitsune::class),
            IsKitsuneHelper::class,
            true
        )) {
            return $helperClass;
        }

        throw new InvalidKitsuneHelperException($helperClass);
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
            ->aliasMiddleware('kitsune.layout', KitsuneLayoutMiddleware::class)
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
            __DIR__.'/../config/kitsune-namespace.php' => config_path('kitsune/packages/kitsune.php'),
            __DIR__.'/../config/kitsune-core.php' => config_path('kitsune/core.php'),
            __DIR__.'/../config/kitsune-view.php' => config_path('kitsune/view.php'),
        ], 'kitsune.config');
    }

    protected function addEventListeners()
    {
        Event::listen(KitsuneCoreInitialized::class, CoreInitialized::class);
        Event::listen(KitsuneCoreUpdated::class, HandleCoreUpdate::class);
        Event::listen(KitsuneSourceNamespaceUpdated::class, UpdateKitsuneForNamespace::class);
        Event::listen(KitsuneSourceRepositoryUpdated::class, PropagateSourceUpdate::class);
    }
}
