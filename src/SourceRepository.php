<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;
use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

class SourceRepository implements IsSourceRepository
{
    use UtilisesKitsune;

    protected ?IsSourceNamespace $namespace = null;

    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
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
    }

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
    public function assignNamespace(IsSourceNamespace $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority|null $priority): bool
    {
        if(!is_a($priority, DefinesPriority::class)) {
            $priority = $this->getDefaultPriority($priority);
        }

        if ($this->priority->getValue() !== $priority->getValue()) {
            $this->priority = $priority;

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
     * @param  string  $path
     * @return bool
     */
    public function prependPath(string $path): bool
    {
        return $this->addPath($path, true);
    }

    /**
     * Register a path as source.
     *
     * @param  string  $path
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string $path, bool $prepend = false): bool
    {
        if (!in_array($path, $this->paths, true)) {
            $prepend ? array_unshift($this->paths, $path) : $this->paths[] = $path;

            return true;
        }

        return false;
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
}
