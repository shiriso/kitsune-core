<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;
use Shiriso\Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

class SourceRepository implements IsSourceRepository
{
    use UtilisesKitsune;

    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
        protected IsSourceNamespace $namespace,
        protected string $alias,
        protected ?string $basePath = null,
        protected ?array $paths = null,
        protected string|DefinesPriority|null $priority = null
    ) {
        $this->basePath ??= $this->getBasePath();
        $this->paths ??= $this->getDefaultPaths();

        if (!is_a($this->priority, DefinesPriority::class)) {
            $this->priority = $this->getDefaultPriority($this->priority);
        }

        $this->dispatchRepositoryUpdatedEvent();
    }

    /**
     * Get the namespace the source is registered for.
     *
     * @return IsSourceNamespace
     */
    public function getNamespace(): IsSourceNamespace
    {
        return $this->namespace;
    }

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority|null $priority): bool
    {
        if (!is_a($priority, DefinesPriority::class)) {
            $priority = $this->getDefaultPriority($priority);
        }

        if ($this->priority->getValue() !== $priority->getValue()) {
            $this->priority = $priority;

            $this->dispatchRepositoryUpdatedEvent();

            return true;
        }

        return false;
    }

    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority
    {
        return $this->priority;
    }

    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string|array  $path
     * @return bool
     */
    public function prependPath(string|array $path): bool
    {
        return $this->addPath($path, true);
    }

    /**
     * Register a path as source.
     *
     * @param  string|array  $path
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string|array $path, bool $prepend = false): bool
    {
        $updated = false;

        foreach (Arr::wrap($path) as $path) {
            if (!in_array($path, $this->paths, true)) {
                $prepend ? array_unshift($this->paths, $path) : $this->paths[] = $path;

                $updated = true;
            }
        }

        $updated && $this->dispatchRepositoryUpdatedEvent();

        return $updated;
    }

    /**
     * Get the source paths which have been registered in the repository.
     *
     * @return array
     */
    public function getPaths(): array
    {
        $basePath = $this->getBasePath();

        return array_map(fn($path) => $basePath.$path, $this->paths);
    }

    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     */
    public function getRegisteredPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get the base directory for the current source.
     *
     * @return string
     * @throws MissingBasePathException
     */
    public function getBasePath(): string
    {
        if ($basePath = $this->basePath ?? $this->getDefaultValue('basePath')) {
            return Str::finish($basePath, '/');
        }

        throw new MissingBasePathException($this->alias);
    }

    /**
     * Get the default paths configured for the source.
     *
     * @return array
     */
    protected function getDefaultPaths(): array
    {
        return Arr::wrap($this->getDefaultValue('paths', []));
    }

    /**
     * Get the default priority for the source based on a given priority or the global default.
     *
     * @param  string|null  $priority
     * @return DefinesPriority
     */
    protected function getDefaultPriority(?string $priority = null): DefinesPriority
    {
        return $this->getKitsuneHelper()->getPriorityDefault($priority ?? $this->getDefaultValue('priority', 'source'));
    }

    /**
     * Get the default value based on the global default source configurations.
     *
     * @param  string  $key
     * @param  string|array|DefinesPriority|null  $default
     * @return string|array|DefinesPriority|null
     */
    protected function getDefaultValue(
        string $key,
        string|array|DefinesPriority|null $default = null
    ): string|array|DefinesPriority|null {
        return $this->getKitsuneHelper()->getDefaultSourceConfiguration($this->alias)[$key] ?? $default;
    }

    /**
     * Dispatches the KitsuneSourceRepositoryUpdated event.
     *
     * @return void
     */
    protected function dispatchRepositoryUpdatedEvent(): void
    {
        KitsuneSourceRepositoryUpdated::dispatch($this);
    }

}
