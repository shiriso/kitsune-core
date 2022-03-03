<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsKitsuneCore;
use Kitsune\Core\Contracts\IsKitsuneManager;
use Kitsune\Core\Events\KitsuneCoreInitialized;
use Kitsune\Core\Events\KitsuneCoreUpdated;
use Kitsune\Core\Events\KitsuneManagerInitialized;
use Kitsune\Core\Events\KitsuneSourceNamespaceCreated;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Kitsune\Core\Events\KitsuneSourceRepositoryCreated;
use Kitsune\Core\Tests\AbstractTestCase;

class KitsuneCoreTest extends AbstractTestCase
{
    use UtilisesKitsune;

    protected static IsKitsuneCore $core;
    protected static IsKitsuneManager $manager;

    protected function getEnvironmentSetUp($app)
    {
        Config::set('kitsune.core.auto_initialize', false);
        Config::set('kitsune.core.auto_refresh', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('kitsune', static::$core ??= app('kitsune'));
        app()->instance('kitsune.manager', static::$manager ??= app('kitsune.manager'));
    }

    /**
     * @test
     * @return void
     */
    public function isNotInitializedWhenAutoInitializeIsDisabled(): void
    {
        $this->assertFalse($this->getKitsuneCore()->isInitialized());
    }

    /**
     * @test
     * @return void
     */
    public function validateCoreDefaults(): void
    {
        $core = $this->getKitsuneCore();

        $this->assertFalse($core->globalModeEnabled());
        $this->assertFalse($core->shouldAutoRefresh());
        $this->assertNull($core->getGlobalNamespace());
        $this->assertNull($core->getApplicationLayout());
    }

    /**
     * @test
     * @return void
     */
    public function initializationTriggersExpectedEvents(): void
    {
        $core = $this->getKitsuneCore();

        Event::fake();

        $core->initialize();

        Event::assertDispatched(KitsuneCoreInitialized::class);
        Event::assertDispatched(KitsuneManagerInitialized::class);
        Event::assertDispatched(KitsuneSourceNamespaceCreated::class, 2);
        Event::assertDispatched(KitsuneSourceRepositoryCreated::class, 5);
    }

    /**
     * @test
     * @depends initializationTriggersExpectedEvents
     * @returns array
     */
    public function initializationOnlyRunsOnce(): void
    {
        Event::fake();

        $core = $this->getKitsuneCore();

        $this->assertTrue($core->isInitialized());
        $core->initialize();

        Event::assertNothingDispatched();
    }

    /**
     * @test
     * @depends initializationTriggersExpectedEvents
     * @return void
     */
    public function configuresViewFinderForNamespace(): void
    {
        $core = $this->getKitsuneCore();
        $manager = $this->getKitsuneManager();
        $namespace = $manager->getNamespace('kitsune');

        $core->disableGlobalMode();
        $this->assertFalse($core->globalModeEnabled());
        $this->assertTrue($core->refreshNamespacePaths($namespace));
        $this->assertSame(
            $namespace->getPathsWithDerivatives(),
            $core->getViewNamespacePaths($namespace)
        );
        $this->assertNotSame($namespace->getPathsWithDerivatives(true), $core->getViewPaths());
    }

    /**
     * @test
     * @depends configuresViewFinderForNamespace
     * @return void
     */
    public function canConfigureGlobalMode(): void
    {
        $core = $this->getKitsuneCore();
        $namespace = $this->getKitsuneManager()->getNamespace('kitsune');

        $core->disableGlobalMode();
        $core->setGlobalNamespace(null);
        $this->assertFalse($core->globalModeEnabled());

        Event::fakeFor(
            function () use ($core, $namespace) {
                $this->assertTrue($core->enableGlobalMode());

                Event::assertDispatched(KitsuneCoreUpdated::class);
            }
        );

        Event::fakeFor(
            function () use ($core, $namespace) {
                $this->assertTrue($core->setGlobalNamespace($namespace));

                Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);
            }
        );

        $this->assertSame($namespace, $core->getGlobalNamespace());
        $this->assertSame($namespace->getPathsWithDerivatives(), $core->getViewNamespacePaths($namespace));
    }

    /**
     * @test
     * @depends canConfigureGlobalMode
     * @return void
     */
    public function namespaceDoesUpdateGlobalViews(): void
    {
        $core = $this->getKitsuneCore();
        $namespace = $this->getKitsuneManager()->getNamespace('kitsune');

        $core->enableGlobalMode();
        $core->setGlobalNamespace($namespace);
        $this->assertTrue($core->refreshNamespacePaths($namespace));
        $this->assertSame(
            $namespace->getPathsWithDerivatives(),
            $core->getViewNamespacePaths($namespace)
        );
        $this->assertSame($namespace->getPathsWithDerivatives(true), $core->getViewPaths());
    }

    /**
     * @test
     * @depends canConfigureGlobalMode
     * @return void
     */
    public function canAutomaticallyResetGlobalViews(): void
    {
        $core = $this->getKitsuneCore();
        $namespace = $this->getKitsuneManager()->getNamespace('kitsune');

        $this->assertSame(
            $namespace->getPathsWithDerivatives(),
            $core->getViewNamespacePaths($namespace)
        );
        $this->assertSame($namespace->getPathsWithDerivatives(true), $core->getViewPaths());
        $this->assertFalse($core->configureGlobalViewFinder($namespace));
        $this->assertTrue($core->disableGlobalMode());

        $this->assertSame(config('view.paths'), $core->getViewPaths());
    }

    /**
     * @test
     * @depends canConfigureGlobalMode
     * @return void
     */
    public function automaticGlobalModeResetCanBeDisabled(): void
    {
        Config::set('kitsune.core.global_mode.reset_on_disable', false);

        $core = $this->getKitsuneCore();
        $namespace = $this->getKitsuneManager()->getNamespace('kitsune');

        $core->enableGlobalMode();
        $core->setGlobalNamespace($namespace);
        $this->assertSame($namespace->getPathsWithDerivatives(true), $core->getViewPaths());
        $this->assertFalse($core->configureGlobalViewFinder($namespace));
        $this->assertTrue($core->disableGlobalMode());

        $this->assertSame($core->getViewPaths(), $namespace->getPathsWithDerivatives(true));
    }

}
