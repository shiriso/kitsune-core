<?php

namespace Shiriso\Kitsune\Core\Contracts;

use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

interface IsSourceRepository
{
    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
        IsSourceNamespace $namespace,
        string $alias,
        ?string $basePath = null,
        ?array $paths = null,
        string|DefinesPriority|null $priority = null
    );

    /**
     * Get the base directory for the current source.
     *
     * @return string
     * @throws MissingBasePathException
     */
    public function getBasePath(): string;

    /**
     * Register a path as source.
     *
     * @param  string|array  $path
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string|array $path, bool $prepend = false): bool;

    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string|array  $path
     * @return bool
     */
    public function prependPath(string|array $path): bool;

    /**
     * Get the source paths which have been registered in the repository.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority|null $priority): bool;

    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     */
    public function getRegisteredPaths(): array;

    /**
     * Get the namespace the source is registered for.
     *
     * @return IsSourceNamespace
     */
    public function getNamespace(): IsSourceNamespace;
}
