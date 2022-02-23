<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Events\KitsuneSourceRepositoryCreated;
use Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\Exceptions\MissingBasePathException;
use Kitsune\Core\Listeners\PropagateSourceUpdate;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class SourceRepositoryTest extends AbstractNamespaceTestCase
{
    use UtilisesKitsune;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @return IsSourceNamespace
     */
    public function canBeCreatedUsingDefaults(): IsSourceNamespace
    {
        return $this->createNamespace('default');
    }

    /**
     * @test
     * @depends canBeCreatedUsingDefaults
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasValidDefaultConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        return $this->validatesNamespaceConfiguration($namespace);
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasDefaultSourceRepositories(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertContains('vendor', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('vendor'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('vendor'));

        $this->assertContains('published', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('published'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('published'));

        $this->assertFalse($namespace->hasSource('does-not-exist'));

        $this->expectErrorMessage('Undefined array key "does-not-exist"');
        $namespace->getSource('does-not-exist');

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canCreateCustomisedSourceUsingConfigDefaults(IsSourceNamespace $namespace): IsSourceNamespace {
        Config::set(
            'kitsune.view.sources.customised',
            [
                'base_path' => resource_path('customised'),
                'paths' => ['path'],
                'priority' => 'least',
            ]
        );

        Event::fake();

        $namespace->addSource('customised');

        Event::assertDispatched(KitsuneSourceRepositoryCreated::class);
        Event::assertDispatched(KitsuneSourceRepositoryUpdated::class);
        Event::assertListening(KitsuneSourceRepositoryUpdated::class, PropagateSourceUpdate::class);

        return $namespace;
    }

    /**
     * @test
     * @depends canCreateCustomisedSourceUsingConfigDefaults
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasCustomisedSourceRepository(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertContains('customised', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('customised'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('customised'));

        return $namespace;
    }

    /**
     * @test
     * @depends hasCustomisedSourceRepository
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function customisedSourceIsUsingDefaultsFromConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('customised');

        $this->assertSame('customised', $source->getName());
        $this->assertEquals(resource_path('customised/'), $source->getBasePath());
        $this->assertEquals(['path'], $source->getRegisteredPaths());
        $this->hasValidPriority('least', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return void
     */
    public function canAddSourceWithOnlyBasePath(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->addSource('base-path-only', basePath: resource_path('base-path-only'));

        $this->assertInstanceOf(IsSourceRepository::class, $source);

        return $namespace;
    }

    /**
     * @test
     * @depends canAddSourceWithOnlyBasePath
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasBasePathOnlySourceRepository(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertContains('base-path-only', $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('base-path-only'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('base-path-only'));

        return $namespace;
    }

    /**
     * @test
     * @depends hasBasePathOnlySourceRepository
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function basePathOnlySourceIsUsingDefaults(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('base-path-only');

        $this->assertSame('base-path-only', $source->getName());
        $this->assertEquals(resource_path('base-path-only/'), $source->getBasePath());
        $this->assertEquals([], $source->getRegisteredPaths());
        $this->hasValidPriority('source', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function exceptionOnMissingBasePath(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Config::set('kitsune.view.sources.missing-base-path', []);

        $this->expectException(MissingBasePathException::class);

        $namespace->addSource('missing-base-path');

        return $namespace;
    }
}
