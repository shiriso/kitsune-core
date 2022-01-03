<?php

namespace Kitsune\Core\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Kitsune\Core\Events\KitsuneSourceRepositoryCreated;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\SourceNamespace;
use Kitsune\Core\Tests\AbstractTestCase;

class KitsuneNamespaceTest extends AbstractTestCase
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
        $this->hasValidPriority($namespace->getPriority(), 'namespace');
        $this->assertNull($namespace->getLayout());
        $this->assertEqualsCanonicalizing(['vendor', 'published'], $namespace->getRegisteredSources());
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
        Event::fakeFor(function () use ($namespace) {
            $namespace->addPath($this->namespaceAppendPathArray());

            Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

            return $namespace;
        });

        $expectedPaths = [$this->namespacePrependPath(), $this->namespaceAppendPath(), ...$this->namespaceAppendPathArray()];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

        return $namespace;
    }

    /**
     * @test
     * @depends canPrependPath
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function canPrependPathArray(IsSourceNamespace $namespace): IsSourceNamespace
    {
        Event::fakeFor(function () use ($namespace) {
            $namespace->prependPath($this->namespacePrependPathArray());

            Event::assertDispatched(KitsuneSourceNamespaceUpdated::class);

            return $namespace;
        });

        $expectedPaths = [...$this->namespacePrependPathArray(), $this->namespacePrependPath(), $this->namespaceAppendPath(), ...$this->namespaceAppendPathArray()];

        $this->assertEquals($expectedPaths, $namespace->getRegisteredPaths());

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
     * Helper to create namespaces validating basics for various configurations.
     *
     * @param  string  $name
     * @param  array  $configuration
     * @return IsSourceNamespace
     */
    protected function createNamespace(string $name, array $configuration = []): IsSourceNamespace
    {
        $namespace = Event::fakeFor(function () use ($name, $configuration) {
            $namespace = new SourceNamespace($name, ...$configuration);

            Event::assertDispatched(KitsuneSourceRepositoryCreated::class);

            return $namespace;
        });

        $this->assertIsObject($namespace);

        return $namespace;
    }

    /**
     * @param  DefinesPriority  $priority
     * @param  string  $expectedValue
     * @return void
     */
    protected function hasValidPriority(DefinesPriority $priority, string $expectedValue): void
    {
        $this->assertEquals(
            app('kitsune.helper')->getPriorityDefault($expectedValue)->getValue(),
            $priority->getValue()
        );
    }

    /**
     * Generates the resource path to test appends.
     *
     * @return string
     */
    protected function namespaceAppendPath(): string
    {
        return resource_path('views/namespace/append');
    }

    /**
     * Generates the resource path to test prepends.
     *
     * @return string
     */
    protected function namespacePrependPath(): string
    {
        return resource_path('views/namespace/prepend');
    }

    /**
     * Generates a list of resource paths to test appending.
     *
     * @return array
     */
    protected function namespaceAppendPathArray(): array
    {
        return [
            resource_path('views/namespace/existing/append-array'),
            resource_path('views/namespace/virtual/append-array'),
        ];
    }

    /**
     * Generates a list of resource paths to test prepending.
     *
     * @return array
     */
    protected function namespacePrependPathArray(): array
    {
        return [
            resource_path('views/namespace/existing/prepend-array'),
            resource_path('views/namespace/virtual/prepend-array'),
        ];
    }
}
