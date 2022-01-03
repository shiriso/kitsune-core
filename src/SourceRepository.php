<?php

namespace Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kitsune\Core\Concerns\ManagesPaths;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Kitsune\Core\Exceptions\MissingBasePathException;

class SourceRepository implements IsSourceRepository
{
    use ManagesPaths;
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
