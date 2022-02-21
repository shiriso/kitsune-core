<?php

namespace Kitsune\Core\Tests;

use Illuminate\Support\Facades\Event;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Events\KitsuneSourceRepositoryCreated;
use Kitsune\Core\SourceNamespace;

abstract class AbstractNamespaceTestCase extends AbstractTestCase
{
    protected int $expectedSourceCreatedEvents = 2;
    protected string $expectedPriority = 'namespace';
    protected ?string $expectedLayout = null;
    protected array $expectedPaths = [];
    protected bool $expectedIncludeDefaults = false;

    /**
     * Helper to create namespaces validating basics for various configurations.
     *
     * @param  string  $name
     * @param  array  $configuration
     * @return IsSourceNamespace
     */
    protected function createNamespace(string $name, array $configuration = []): IsSourceNamespace
    {
        Event::fake(KitsuneSourceRepositoryCreated::class);

        $namespace = new SourceNamespace($name, ...$configuration);

        Event::assertDispatched(KitsuneSourceRepositoryCreated::class, $this->expectedSourceCreatedEvents);

        // As events and listeners will not be executed due to faking and tests,
        // we need to trigger the update manually to make sure everything
        // is up-to-date which would get triggered by the listener.
        $namespace->dispatchUpdatedEvent();

        $this->assertIsObject($namespace);

        return $namespace;
    }

    /**
     * @param  DefinesPriority  $priority
     * @param  string  $expectedValue
     * @return void
     */
    protected function hasValidPriority(string $expectedValue, DefinesPriority $priority): void
    {
        $this->assertEquals(
            app('kitsune.helper')->getPriorityDefault($expectedValue)->getValue(),
            $priority->getValue()
        );
    }

    /**
     * Validates that the configuration matches our configured expectations.
     *
     * @param  IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    protected function validatesNamespaceConfiguration(IsSourceNamespace $namespace): IsSourceNamespace
    {
        $this->hasValidPriority($this->getExpectedPriority(), $namespace->getPriority());
        $this->assertEquals($this->getExpectedLayout(), $namespace->getLayout());
        $this->assertEquals($this->getExpectedPaths(), $namespace->getRegisteredPaths());
        $this->assertEquals($this->getExpectedIncludeDefaults(), $namespace->shouldIncludeDefaults());

        return $namespace;
    }

    /**
     * Returns the expected layout after initialisation.
     *
     * @return string|null
     */
    protected function getExpectedLayout(): ?string
    {
        return $this->expectedLayout;
    }

    /**
     * Returns the expected layout after initialisation.
     *
     * @return string
     */
    protected function getExpectedPriority(): string
    {
        return $this->expectedPriority;
    }

    /**
     * Returns the expected layout after initialisation.
     *
     * @return array|null
     */
    protected function getExpectedPaths(): ?array
    {
        return $this->expectedPaths;
    }

    /**
     * Returns the expected layout after initialisation.
     *
     * @return bool
     */
    protected function getExpectedIncludeDefaults(): bool
    {
        return $this->expectedIncludeDefaults;
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
            resource_path('views/namespace/fake/append-array'),
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
            resource_path('views/namespace/fake/prepend-array'),
        ];
    }

    /**
     * Get the default relative path used for sources.
     *
     * @return string
     */
    protected function sourceDefaultPath(): string
    {
        return 'path';
    }

    /**
     * Get the compiled path for a given source based on the source's basePath and given path.
     *
     * @param  IsSourceRepository  $source
     * @param  string|null  $path
     * @return string
     */
    protected function sourcePath(IsSourceRepository $source, string $path = null): string
    {
        return $source->getBasePath().($path ?? $this->sourceDefaultPath());
    }
}
