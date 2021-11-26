<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\ServiceProvider;

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

        app('kitsune')->refreshViewSources();
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

        $this->registerKitsune();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['kitsune'];
    }

    /**
     * Register Kitsune as a singleton.
     *
     * @return void
     */
    protected function registerKitsune()
    {
        $this->app->singleton('kitsune', function () {
            return new (config('kitsune.core.helper', Kitsune::class))();
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
}