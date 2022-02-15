<?php

namespace Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kitsune\Core\Concerns\HasPriority;
use Kitsune\Core\Concerns\ManagesPaths;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\ImplementsPriority;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Events\KitsuneSourceRepositoryUpdated;
use Kitsune\Core\Exceptions\MissingBasePathException;

class SourceRepository implements IsSourceRepository
{
    use HasPriority;
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

        $this->setPriority($this->priority);
        $this->dispatchUpdatedEvent();
    }

    /**
     * Returns the Name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->alias;
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
            return Str::finish($basePath, DIRECTORY_SEPARATOR);
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
    protected function dispatchUpdatedEvent(): void
    {
        KitsuneSourceRepositoryUpdated::dispatch($this);
    }

}
