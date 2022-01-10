<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\Exceptions\MissingPathsPropertyException;

interface CanManagePaths
{
    /**
     * Register a path as source.
     *
     * If a path was added, it will dispatch also automatically dispatch
     * an updated event for the resource, if the "dispatchUpdatedEvent"
     * method exists in the current context.
     *
     * @param  string|array  $path
     * @param  bool  $prepend
     * @return bool
     * @throws MissingPathsPropertyException
     */
    public function addPath(string|array $path, bool $prepend = false): bool;


    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string|array  $path
     * @return bool
     */
    public function prependPath(string|array $path): bool;


    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     */
    public function getRegisteredPaths(): array;
}
