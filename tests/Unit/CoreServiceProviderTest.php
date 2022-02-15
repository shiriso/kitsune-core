<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Kitsune\Core\Exceptions\InvalidKitsuneHelperException;
use Kitsune\Core\KitsuneCoreServiceProvider;
use Kitsune\Core\KitsuneHelper;
use Kitsune\Core\Tests\AbstractTestCase;

class CoreServiceProviderTest extends AbstractTestCase
{
    protected object $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = app()->make(KitsuneCoreServiceProvider::class, ['app' => app()]);
    }

    /**
     * @test
     * @return void
     */
    public function providesAllServices(): void
    {
        $this->assertEqualsCanonicalizing(
            ['kitsune', 'kitsune.helper', 'kitsune.manager'],
            $this->serviceProvider->provides()
        );
    }

    /**
     * @test
     * @return void
     */
    public function usesHelperFromConfig(): void
    {
        $this->assertEquals(config('kitsune.core.service.helper'), $this->serviceProvider->getHelperClass());
    }

    /**
     * @test
     * @return void
     */
    public function fallsBackToDefaultHelper(): void
    {
        $serviceConfig = config('kitsune.core.service');
        unset($serviceConfig['helper']);
        Config::set('kitsune.core.service', $serviceConfig);

        $this->assertEquals(KitsuneHelper::class, $this->serviceProvider->getHelperClass());
    }

    /**
     * @test
     * @return void
     */
    public function exceptionIfInterfaceIsMissing(): void
    {
        Config::set('kitsune.core.service.helper', null);

        $this->expectException(InvalidKitsuneHelperException::class);
        $this->serviceProvider->getHelperClass();
    }
}
