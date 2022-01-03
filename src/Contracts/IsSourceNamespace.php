<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\SourceRepository;

interface IsSourceNamespace
{
    public function __construct(
        string $namespace,
        bool $addDefaults = false,
        string|DefinesPriority|null $priority = null,
        ?string $layout = null,
        string|array $paths = []
    );

    /**
     * Prepend a source path for the given repository.
     *
     * @param  string|array  $path
     * @param  string|array  $sourceRepository
     * @return bool
     */
    public function prependPathToSource(string|array $path, string|array $sourceRepository): bool;

    /**
     * Determine if the namespace has the specific source.
     *
     * @param  string  $sourceRepository
     * @return bool
     */
    public function hasSource(string $sourceRepository): bool;

    /**
     * Get the priority of the namespace.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

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
     * Set a new priority.
     *
     * @param  string|DefinesPriority  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority $priority): bool;

    /**
     * Determines the update state and sets it the namespace.
     *
     * As the nested mutators to sources and similar will return
     * if the setter actually changed something, we do not want
     * to set the hasUpdates every time a setter was called,
     * but only when there was an actual change to it.
     *
     * @param  bool  $state
     * @return bool
     */
    public function setUpdateState(bool $state = true): bool;

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
     * @param  string|array  $sourceRepository
     * @param  bool  $prepend
     * @return bool
     */
    public function addPathToSource(string|array $path, string|array $sourceRepository, bool $prepend = false): bool;

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
     * @param  bool  $addDefaultPaths  When true, it will add the applications default paths from Laravel
     * @return array
     */
    public function getPathsWithDerivatives(bool $addDefaultPaths = false): array;

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
}
