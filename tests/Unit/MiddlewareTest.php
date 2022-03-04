<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Tests\AbstractTestCase;

class MiddlewareTest extends AbstractTestCase
{
    use UtilisesKitsune;

    protected function getEnvironmentSetUp($app)
    {
        Config::set('kitsune.core.auto_initialize', false);
    }

    /**
     * @test
     * @return void
     */
    public function notInitializedWithoutAutoInitializeOrMiddleware(): void
    {
        Route::get('welcome')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', false);
        $response->assertHeader('kitsune-global-enabled', false);
        $response->assertHeader('kitsune-global-namespace');
        $response->assertHeader('kitsune-app-layout');
        $response->assertSee('Laravel 5', true);
    }

    /**
     * @test
     * @return void
     */
    public function middlewareCanActivateKitsune(): void
    {
        Route::get('welcome')->middleware('kitsune')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', true);
        $response->assertHeader('kitsune-global-enabled', false);
        $response->assertHeader('kitsune-global-namespace');
        $response->assertHeader('kitsune-app-layout');
        $response->assertSee('Laravel 5', true);
    }

    /**
     * @test
     * @return void
     */
    public function middlewareCanEnableGlobalMode(): void
    {
        Route::get('welcome')->middleware('kitsune.global')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('kitsune'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', true);
        $response->assertHeader('kitsune-global-enabled', true);
        $response->assertHeader('kitsune-global-namespace', 'kitsune');
        $response->assertHeader('kitsune-app-layout');
        $response->assertSee('Kitsune Default Path', true);
    }

    /**
     * @test
     * @return void
     */
    public function middlewareCanDefineGlobalMode(): void
    {
        Route::get('welcome')->middleware('kitsune.global:package')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', true);
        $response->assertHeader('kitsune-global-enabled', true);
        $response->assertHeader('kitsune-global-namespace', 'package');
        $response->assertHeader('kitsune-app-layout');
        $response->assertSee('Package Testing Layout', true);
    }

    /**
     * @test
     * @return void
     */
    public function middlewareCanDisableGlobalMode(): void
    {
        Route::get('welcome')->middleware('kitsune.global:false')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', false);
        $response->assertHeader('kitsune-global-enabled', false);
        $response->assertHeader('kitsune-global-namespace');
        $response->assertHeader('kitsune-app-layout');
    }


    /**
     * @test
     * @return void
     */
    public function middlewareCanDefineAppLayout(): void
    {
        Route::get('welcome')->middleware('kitsune.layout:app-layout')->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', false);
        $response->assertHeader('kitsune-global-enabled', false);
        $response->assertHeader('kitsune-global-namespace');
        $response->assertHeader('kitsune-app-layout', 'app-layout');
        $response->assertSee('Laravel 5');
    }

    /**
     * @test
     * @return void
     */
    public function canCombineLayoutAndGlobalMiddleware(): void
    {
        Route::get('welcome')->middleware(['kitsune.global', 'kitsune.layout:app-layout'])->name('welcome')->uses(fn() => $this->responseWithKitsuneInfoHeaders('welcome'));

        $response = $this->get('/welcome');
        $response->assertHeader('kitsune-initialized', true);
        $response->assertHeader('kitsune-global-enabled', true);
        $response->assertHeader('kitsune-global-namespace');
        $response->assertHeader('kitsune-app-layout', 'app-layout');
        $response->assertSee('Default App Layout');
    }



    /**
     * @param  string  $view
     * @return \Illuminate\Http\Response
     */
    protected function responseWithKitsuneInfoHeaders(string $view): \Illuminate\Http\Response
    {
        return Response::view($view)->withHeaders([
            'kitsune-initialized' => app('kitsune')->isInitialized(),
            'kitsune-global-enabled' => app('kitsune')->globalModeEnabled(),
            'kitsune-global-namespace' => app('kitsune')->getGlobalNamespace()?->getName(),
            'kitsune-app-layout' => app('kitsune')->getApplicationLayout(),
        ]);
    }
}
