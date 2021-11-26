<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Exceptions\MissingBasePathException;

abstract class SourcePathRepository
{
    protected array $sourcePaths;

    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(protected string $alias, array $sourcePaths = null, protected ?string $basePath = null)
    {
        $this->sourcePaths = $sourcePaths ?? $this->getDefaultPaths();
        $this->basePath = $this->getBasePath();
    }

    /**
     * Prepend a path to the registered $vendorPaths.
     *
     * @param  string  $sourcePath
     * @return bool
     */
    public function prependSource(string $sourcePath): bool
    {
        return $this->addSource($sourcePath, true);
    }

    /**
     * Register a path as source.
     *
     * @param  string  $sourcePath
     * @param  bool  $prepend
     * @return bool
     */
    public function addSource(string $sourcePath, bool $prepend = false): bool
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
    public function getSourcePaths(): array
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
    protected function getBasePath(): string
    {
        $basePath = $this->basePath ?? config(sprintf('kitsune.view.extra_sources.%s.source', $this->alias));

        if ($basePath) {
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
        return $this->alias ? Arr::wrap(config(sprintf('kitsune.view.extra_sources.%s.paths', $this->alias), [])) : [];
    }
}