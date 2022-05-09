<?php

namespace Kitsune\Core\Concerns;

use Kitsune\Core\Exceptions\MissingPathsPropertyException;
use Illuminate\Support\Arr;

trait ManagesPaths
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
    public function addPath(string|array $path, bool $prepend = false): bool
    {
        $this->validateManagePathsIntegration();

        $updated = false;

        $newPaths = Arr::wrap($path);

        foreach ($prepend ? array_reverse($newPaths) : $newPaths as $path) {
            if (!in_array($path, $this->paths, true)) {
                $prepend ? array_unshift($this->paths, $path) : $this->paths[] = $path;

                $updated = true;
            }
        }

        $updated && method_exists($this, 'dispatchUpdatedEvent') && $this->dispatchUpdatedEvent();

        return $updated;
    }

    /**
     * Prepend a path to the registered source paths.
     *
     * @param  string|array  $path
     * @return bool
     */
    public function prependPath(string|array $path): bool
    {
        return $this->addPath($path, true);
    }

    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     * @throws MissingPathsPropertyException
     */
    public function getRegisteredPaths(): array
    {
        $this->validateManagePathsIntegration();

        return $this->paths;
    }

    /**
     * Validates that all necessary properties exist in the class.
     *
     * @return void
     * @throws MissingPathsPropertyException
     */
    protected function validateManagePathsIntegration(): void
    {
        if (!property_exists($this, 'paths')) {
            throw new MissingPathsPropertyException(static::class);
        }
    }
}
