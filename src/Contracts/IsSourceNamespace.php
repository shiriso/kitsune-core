<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\SourceRepository;

interface IsSourceNamespace extends CanManagePaths, ImplementsPriority
{
    public function __construct(
        string $namespace,
        bool $includeDefaults = false,
        string|DefinesPriority|null $priority = null,
        ?string $layout = null,
        string|array $paths = []
    );

    /**
     * Prepend a source path for the given repository.
     *
     * @param  string|array  $path
     * @param  string  $sourceRepository
     * @return bool
     */
    public function prependPathToSource(string|array $path, string $sourceRepository): bool;

    /**
     * Determine if the namespace has the specific source.
     *
     * @param  string  $sourceRepository
     * @return bool
     */
    public function hasSource(string $sourceRepository): bool;

    /**
     * Add a new SourceRepository to the current SourceNamespace.
     *
     * Keep in mind that the $basePath is required, if the source is not defined in the config.
     *
     * @param  string  $sourceRepository
     * @param  string|null  $basePath
     * @param  array|null  $paths
     * @param  string|DefinesPriority|null  $priority
     * @return SourceRepository
     */
    public function addSource(
        string $sourceRepository,
        string $basePath = null,
        array $paths = null,
        string|DefinesPriority|null $priority = null,
    ): SourceRepository;

    /**
     * Compile a list of possible view source paths sorted by their priority.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Determines the update state and dispatches the event if something changed.
     *
     * As nested resources may propagate their changes by using events or
     * similar, we offer the possibility to pass if something updated.
     *
     * @param  bool  $updated
     * @return bool
     */
    public function dispatchUpdatedEvent(bool $updated = true): bool;

    /**
     * Get the currently activated layout.
     *
     * @return string|null
     */
    public function getLayout(): ?string;

    /**
     * Get the current priority of the given source.
     *
     * @param  string  $source
     * @return DefinesPriority
     */
    public function getSourcePriority(string $source): DefinesPriority;

    /**
     * Register a source path for the given repository.
     *
     * @param  string|array  $path
     * @param  string  $sourceRepository
     * @param  bool  $prepend
     * @return bool
     */
    public function addPathToSource(string|array $path, string $sourceRepository, bool $prepend = false): bool;

    /**
     * Set a new active layout.
     *
     * @param  string  $layout
     * @return bool
     */
    public function setLayout(string $layout): bool;

    /**
     * Get a compiled list of paths from the namespace, sources and potentially Laravel's application
     * paths with derivatives for the application layout and the namespace's layout.
     *
     * @param  bool  $includeDefaultPaths  When true, it will add the applications default view paths
     * @return array
     */
    public function getPathsWithDerivatives(bool $includeDefaultPaths = false): array;

    /**
     * Get the repository for the alias or create a new one if it does not exist.
     *
     * Implicit creation only works for sources which are defined in the config.
     *
     * @param  string  $sourceRepository
     * @return SourceRepository
     */
    public function getSource(string $sourceRepository): SourceRepository;

    /**
     * Returns the name of the current namespace.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set a priority for the given source.
     *
     * @param  string  $source
     * @param  string|DefinesPriority  $priority
     * @return $this
     */
    public function setSourcePriority(string $source, string|DefinesPriority $priority): static;

    /**
     * Get a list of registered source repositories.
     *
     * @return array
     */
    public function getRegisteredSources(): array;

    /**
     * Defines if the default view paths should be included or not.
     *
     * @param  bool  $includeDefaults
     * @return bool Returns true if this changed the configuration.
     */
    public function setIncludeDefaults(bool $includeDefaults): bool;

    /**
     * Determines if the namespace is supposed to include the default view sources.
     *
     * @return bool
     */
    public function shouldIncludeDefaults(): bool;

    /**
     * Enables the inclusion of laravel's default view paths.
     *
     * @return bool Returns true if this changed the configuration.
     */
    public function enableIncludeDefaults(): bool;

    /**
     * Disables the inclusion of laravel's default view paths.
     *
     * @return bool Returns true if this changed the configuration.
     */
    public function disableIncludeDefaults(): bool;
}
