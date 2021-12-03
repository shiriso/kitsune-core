<?php

namespace Shiriso\Kitsune\Core\Contracts;

use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

interface IsSourceRepository
{
    /**
     * Set a new priority.
     *
     * @param  DefinesPriority  $priority
     * @return bool
     */
    public function setPriority(DefinesPriority $priority): bool;

    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string  $sourcePath
     * @return bool
     */
    public function prependPath(string $sourcePath): bool;

    /**
     * Register a path as source.
     *
     * @param  string  $sourcePath
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string $sourcePath, bool $prepend = false): bool;

    /**
     * Get the source paths which have been registered in the repository.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     */
    public function getRegisteredPaths(): array;

    /**
     * Get the base directory for the current source.
     *
     * @return string
     * @throws MissingBasePathException
     */
    public function getBasePath(): string;
}