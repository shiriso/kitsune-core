<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;
use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

class SourceRepository implements IsSourceRepository
{
    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
        protected string $alias,
        protected ?string $basePath = null,
        protected ?array $sourcePaths = null,
        protected ?DefinesPriority $priority = null
    ) {
        $this->basePath ??= $this->getBasePath();
        $this->sourcePaths ??= $this->getDefaultPaths();
        $this->priority ??= app('kitsune.helper')->getPriorityDefault('sources');
    }

    /**
     * Set a new priority.
     *
     * @param  DefinesPriority  $priority
     * @return bool
     */
    public function setPriority(DefinesPriority $priority): bool
    {
        if($this->priority !== $priority) {
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
     * @param  string  $sourcePath
     * @return bool
     */
    public function prependPath(string $sourcePath): bool
    {
        return $this->addPath($sourcePath, true);
    }

    /**
     * Register a path as source.
     *
     * @param  string  $sourcePath
     * @param  bool  $prepend
     * @return bool
     */
    public function addPath(string $sourcePath, bool $prepend = false): bool
    {
        if (!in_array($sourcePath, $this->sourcePaths, true)) {
            $prepend ? array_unshift($this->sourcePaths, $sourcePath) : $this->sourcePaths[] = $sourcePath;

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

        return array_map(fn($sourcePath) => $basePath.$sourcePath, $this->sourcePaths);
    }

    /**
     * Get the registered source paths without transformations.
     *
     * @return array
     */
    public function getRegisteredPaths(): array
    {
        return $this->sourcePaths;
    }

    /**
     * Get the base directory for the current source.
     *
     * @return string
     * @throws MissingBasePathException
     */
    public function getBasePath(): string
    {
        if ($basePath = $this->basePath ?? config(sprintf('kitsune.view.sources.%s.base', $this->alias))) {
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
        return Arr::wrap(config(sprintf('kitsune.view.sources.%s.paths', $this->alias), []));
    }
}