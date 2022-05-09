<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\Exceptions\MissingBasePathException;
use Kitsune\Core\Listeners\PropagateSourceUpdate;
use Kitsune\Core\Listeners\UpdateKitsuneForNamespace;
use Kitsune\Core\Tests\AbstractNamespaceTestCase;

class NamespaceInteractionTest extends AbstractNamespaceTestCase
{
    use UtilisesKitsune;

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
        $this->assertFalse($namespace->disableIncludeDefaults());
        $this->assertFalse($namespace->setPriority('namespace'));

        Event::assertNotDispatched(KitsuneSourceNamespaceUpdated::class);

        return $namespace;
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

        $this->assertSame('vendor', $source->getName());
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

        $this->assertSame('published', $source->getName());
        $this->assertEquals(resource_path('views/vendor/'), $source->getBasePath());
        $this->assertEquals([], $source->getRegisteredPaths());
        $this->hasValidPriority('published', $namespace->getSourcePriority('published'));

        return $namespace;
    }

    /**
     * @test
     * @depends publishedSourceHasDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function cantUpdateWithSameSourcePriority(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceRepositoryUpdated::class);

        $namespace->setSourcePriority('published', 'published');

        Event::assertNotDispatched(KitsuneSourceRepositoryUpdated::class);

        return $namespace;
    }

    /**
     * @test
     * @depends hasValidDefaultConfiguration
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canUpdateSourcePriority(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceRepositoryUpdated::class);

        $namespace->setSourcePriority('published', 'least');

        Event::assertDispatched(KitsuneSourceRepositoryUpdated::class);
        Event::assertListening(KitsuneSourceRepositoryUpdated::class, PropagateSourceUpdate::class);

        $this->hasValidPriority('least', $namespace->getSourcePriority('published'));

        return $namespace;
    }

    /**
     * @test
     * @depends canUpdateSourcePriority
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canAppendPathToSource(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceRepositoryUpdated::class);

        $namespace->addPathToSource('fake', 'published');

        Event::assertDispatched(KitsuneSourceRepositoryUpdated::class);
        Event::assertListening(KitsuneSourceRepositoryUpdated::class, PropagateSourceUpdate::class);

        $this->assertEquals(['fake'], $namespace->getSource('published')->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canAppendPathToSource
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canPrependPathToSource(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceRepositoryUpdated::class);

        $namespace->prependPathToSource('kitsune', 'published');

        Event::assertDispatched(KitsuneSourceRepositoryUpdated::class);
        Event::assertListening(KitsuneSourceRepositoryUpdated::class, PropagateSourceUpdate::class);

        $this->assertEquals(['kitsune', 'fake'], $namespace->getSource('published')->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canPrependPathArray
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function sourceTransformsToAbsolutePaths(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $sourceRepository = $namespace->getSource('published');

        $this->assertEquals(
            array_map(
                fn($path) => $this->sourcePath($sourceRepository, $path),
                $sourceRepository->getRegisteredPaths()
            ),
            $sourceRepository->getPaths()
        );

        return $namespace;
    }

    /**
     * @test
     * @depends canPrependPathToSource
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function generatesGroupedPathsByPriority(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $published = $namespace->getSource('published');
        $vendor = $namespace->getSource('vendor');

        $this->assertEqualsCanonicalizing([
            $published->getPriority()->getValue() => $published->getPaths(),
            $vendor->getPriority()->getValue() => $vendor->getPaths(),
            $namespace->getPriority()->getValue() => $namespace->getRegisteredPaths(),
        ], $namespace->getPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends generatesGroupedPathsByPriority
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function compilesExistingOrderedPaths(IsSourceNamespace $namespace): IsSourceNamespace
    {
        // array_values is used as the order of entries is relevant, but the keys are not.
        $this->assertEquals([
            resource_path('views/namespace/existing/prepend-array'),
            resource_path('views/namespace/prepend'),
            resource_path('views/namespace/append'),
            resource_path('views/namespace/existing/append-array'),
            resource_path('views/vendor/kitsune'),
        ], array_values($namespace->getPathsWithDerivatives()));

        return $namespace;
    }

    /**
     * @test
     * @depends compilesExistingOrderedPaths
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canEnableToIncludeDefaults(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fake(KitsuneSourceNamespaceUpdated::class);

        $namespace->enableIncludeDefaults();

        Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);
        Event::assertListening(KitsuneSourceNamespaceUpdated::class, UpdateKitsuneForNamespace::class);

        return $namespace;
    }

    /**
     * @test
     * @depends canEnableToIncludeDefaults
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function compilesExistingOrderedPathsWithDefaults(IsSourceNamespace $namespace): IsSourceNamespace
    {
        // array_values is used as the order of entries is relevant, but the keys are not.
        $this->assertEquals([
            resource_path('views'),
            resource_path('views/namespace/existing/prepend-array'),
            resource_path('views/namespace/prepend'),
            resource_path('views/namespace/append'),
            resource_path('views/namespace/existing/append-array'),
            resource_path('views/vendor/kitsune'),
        ], array_values($namespace->getPathsWithDerivatives()));

        return $namespace;
    }

    /**
     * @test
     * @depends compilesExistingOrderedPathsWithDefaults
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function compilesExistingOrderedPathsWithLayouts(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $appLayout = 'app-layout';
        $namespaceLayout = 'namespace-layout';

        $namespace->setLayout($namespaceLayout);
        $namespace->enableIncludeDefaults();
        $this->getKitsuneCore()->setApplicationLayout($appLayout);

        $this->assertEquals($namespaceLayout, $namespace->getLayout());
        $this->assertEquals($appLayout, $this->getKitsuneCore()->getApplicationLayout());

        // array_values is used as the order of entries is relevant, but the keys are not.
        $this->assertEquals([
            resource_path('views/'.$appLayout),
            resource_path('views/'.$namespaceLayout),
            resource_path('views'),
            resource_path('views/namespace/existing/prepend-array/'.$appLayout),
            resource_path('views/namespace/existing/prepend-array/'.$namespaceLayout),
            resource_path('views/namespace/existing/prepend-array'),
            resource_path('views/namespace/prepend/'.$appLayout),
            resource_path('views/namespace/prepend/'.$namespaceLayout),
            resource_path('views/namespace/prepend'),
            resource_path('views/namespace/append/'.$appLayout),
            resource_path('views/namespace/append/'.$namespaceLayout),
            resource_path('views/namespace/append'),
            resource_path('views/namespace/existing/append-array/'.$appLayout),
            resource_path('views/namespace/existing/append-array/'.$namespaceLayout),
            resource_path('views/namespace/existing/append-array'),
            resource_path('views/vendor/kitsune/'.$appLayout),
            resource_path('views/vendor/kitsune/'.$namespaceLayout),
            resource_path('views/vendor/kitsune'),
        ], array_values($namespace->getPathsWithDerivatives()));

        return $namespace;
    }
}
