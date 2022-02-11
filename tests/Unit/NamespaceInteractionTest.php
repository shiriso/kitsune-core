<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class NamespaceInteractionTest extends AbstractNamespaceTestCase
{
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
        $this->hasValidPriority('namespace', $namespace->getPriority());
        $this->assertNull($namespace->getLayout());
        $this->assertEquals([], $namespace->getRegisteredPaths());
        $this->assertFalse($namespace->shouldIncludeDefaults());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function sameConfigurationDoesNotTriggerUpdates(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceNamespaceUpdated::class);

        $this->assertFalse($namespace->setLayout($namespace->getLayout()));
        $this->assertFalse($namespace->setIncludeDefaults($namespace->shouldIncludeDefaults()));
        $this->assertFalse($namespace->setPriority($namespace->getPriority()));

        Event::assertNotDispatched(KitsuneSourceNamespaceUpdated::class);

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return void
     */
    public function cantAddSourceWithoutDefaults(IsSourceNamespace $namespace): void
    {
        $this->expectException(InvalidDefaultSourceConfiguration::class);

        $vendor = $namespace->addSource('undefined');

        $this->assertIsNotObject($vendor);
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canSetLayout(IsSourceNamespace $namespace): IsSourceNamespace
    {
        return Event::fakeFor(function () use ($namespace) {
            $namespace->setLayout('kitsune');

            Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

            return $namespace;
        });
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canAppendPath(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fakeFor(function () use ($namespace) {
            $namespace->addPath($this->namespaceAppendPath());

            Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

            return $namespace;
        });

        $expectedPaths = [$this->namespaceAppendPath()];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canAppendPath
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canPrependPath(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fakeFor(function () use ($namespace) {
            $namespace->prependPath($this->namespacePrependPath());

            Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

            return $namespace;
        });

        $expectedPaths = [$this->namespacePrependPath(), $this->namespaceAppendPath()];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canPrependPath
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canAppendPathArray(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceNamespaceUpdated::class);

        $namespace->addPath($this->namespaceAppendPathArray());

        Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

        $expectedPaths = [
            $this->namespacePrependPath(),
            $this->namespaceAppendPath(),
            ...$this->namespaceAppendPathArray()
        ];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canAppendPathArray
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canPrependPathArray(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceNamespaceUpdated::class);

        $namespace->prependPath($this->namespacePrependPathArray());

        Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

        $expectedPaths = [
            ...$this->namespacePrependPathArray(),
            $this->namespacePrependPath(),
            $this->namespaceAppendPath(),
            ...$this->namespaceAppendPathArray()
        ];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canPrependPathArray
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function cantAddDuplicatePaths(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceNamespaceUpdated::class);

        $this->assertFalse($namespace->addPath($this->namespaceAppendPath()));
        $this->assertFalse($namespace->addPath($this->namespaceAppendPathArray()));
        $this->assertFalse($namespace->addPath($this->namespacePrependPath()));
        $this->assertFalse($namespace->addPath($this->namespacePrependPathArray()));

        Event::assertNotDispatched(KitsuneSourceNamespaceUpdated::class);

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function hasDefaultSourceRepositories(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->assertEqualsCanonicalizing(['vendor', 'published'], $namespace->getRegisteredSources());
        $this->assertTrue($namespace->hasSource('vendor'));
        $this->assertInstanceOf(config('kitsune.core.service.source'), $namespace->getSource('vendor'));
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
    public function vendorSourceHasDefaultConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('vendor');

        $this->assertEquals(base_path('vendor/'), $source->getBasePath());
        $this->assertEquals([], $source->getRegisteredPaths());
        $this->hasValidPriority('vendor', $source->getPriority());

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function publishedSourceHasDefaultConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $source = $namespace->getSource('published');

        $this->assertEquals(resource_path('views/vendor/'), $source->getBasePath());
        $this->assertEquals([], $source->getRegisteredPaths());
        $this->hasValidPriority('published', $source->getPriority());

        return $namespace;
    }

    /**
     *
     * @return void
     */
    public function canPrependPathToSource()
    {

    }
}
