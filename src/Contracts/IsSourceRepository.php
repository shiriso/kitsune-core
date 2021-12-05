<?php

namespace Shiriso\Kitsune\Core\Contracts;

use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;
use Shiriso\Kitsune\Core\SourceRepository;

interface IsSourceRepository
{
    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
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
     * @param  string  $path
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string $path, bool $prepend = false): bool;

    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string  $path
     * @return bool
     */
    public function prependPath(string $path): bool;

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
     * This method will assign an internal namespace which may be used to
     * automatically report updates of the source to the namespace.
     *
     * This is important to let the namespace know, that it needs to
     * compile a new list of sources when the paths are requested.
     *
     * @param  IsSourceNamespace  $namespace
     * @return SourceRepository
     */
    public function assignNamespace(IsSourceNamespace $namespace): static;
}