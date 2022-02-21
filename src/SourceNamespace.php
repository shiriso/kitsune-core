<?php

namespace Kitsune\Core;

use Illuminate\Support\Arr;
use Kitsune\Core\Concerns\HasPriority;
use Kitsune\Core\Concerns\ManagesPaths;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Events\KitsuneSourceNamespaceUpdated;
use Kitsune\Core\Events\KitsuneSourceRepositoryCreated;

class SourceNamespace implements IsSourceNamespace
{
    use HasPriority;
    use ManagesPaths;
    use UtilisesKitsune;

    protected ?KitsuneManager $manager;
    protected array $sourceRepositories = [];
    protected array $sourcesByPriority = [];
    protected bool $hasUpdates = false;

    public function __construct(
        protected string $namespace,
        protected bool $includeDefaults = false,
        protected string|DefinesPriority|null $priority = null,
        protected ?string $layout = null,
        protected string|array $paths = []
    ) {
        $this->paths = Arr::wrap($this->paths);

        $this->setPriority($this->priority);
        $this->initializeConfiguredSources();
    }

    /**
     * Returns the name of the current namespace.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->namespace;
    }

    /**
     * Enables the inclusion of laravel's default view paths.
     *
     * @return bool Returns true if this changed the configuration.
     */
    public function enableIncludeDefaults(): bool
    {
        return $this->setIncludeDefaults(true);
    }

    /**
     * Disables the inclusion of laravel's default view paths.
     *
     * @return bool Returns true if this changed the configuration.
     */
    public function disableIncludeDefaults(): bool
    {
        return $this->setIncludeDefaults(false);
    }

    /**
     * Defines if the default view paths should be included or not.
     *
     * @param  bool  $includeDefaults
     * @return bool Returns true if this changed the configuration.
     */
    public function setIncludeDefaults(bool $includeDefaults): bool
    {
        if ($this->includeDefaults !== $includeDefaults) {
            $this->includeDefaults = $includeDefaults;

            $this->dispatchUpdatedEvent();

            return true;
        }

        return false;
    }

    /**
     * Determines if the namespace is supposed to include the default view sources.
     *
     * @return bool
     */
    public function shouldIncludeDefaults(): bool
    {
        return $this->includeDefaults;
    }

    /**
     * Set a priority for the given source.
     *
     * @param  string  $source
     * @param  string|DefinesPriority  $priority
     * @return $this
     */
    public function setSourcePriority(string $source, string|DefinesPriority $priority): static
    {
        $this->getSource($source)->setPriority($priority);

        return $this;
    }

    /**
     * Get the current priority of the given source.
     *
     * @param  string  $source
     * @return DefinesPriority
     */
    public function getSourcePriority(string $source): DefinesPriority
    {
        return $this->getSource($source)->getPriority();
    }

    /**
     * Set a new active layout.
     *
     * @param  string|null  $layout
     * @return bool
     */
    public function setLayout(?string $layout): bool
    {
        if ($this->layout !== $layout) {
            $this->layout = $layout;
            $this->dispatchUpdatedEvent();

            return true;
        }

        return false;
    }

    /**
     * Get the currently activated layout.
     *
     * @return string|null
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * Add a new SourceRepository to the current SourceNamespace.
     *
     * Keep in mind that the $basePath is required, if the source is not defined in the config.
     *
     * @param  string  $sourceRepository
     * @param  string|null  $basePath
     * @param  array|null  $paths
     * @param  string|DefinesPriority|null  $priority
     * @return SourceRepository
     */
    public function addSource(
        string $sourceRepository,
        string $basePath = null,
        array $paths = null,
        string|DefinesPriority|null $priority = null,
    ): SourceRepository {
        $repository = $this->sourceRepositories[$sourceRepository] =
            new (app('kitsune.helper')->getSourceRepositoryClass())($this, ...func_get_args());

        KitsuneSourceRepositoryCreated::dispatch($repository);

        return $repository;
    }

    /**
     * Get the repository for the alias.
     *
     * @param  string  $sourceRepository
     * @return SourceRepository
     */
    public function getSource(string $sourceRepository): SourceRepository
    {
        return $this->sourceRepositories[$sourceRepository];
    }

    /**
     * Get a list of registered source repositories.
     *
     * @return array
     */
    public function getRegisteredSources(): array
    {
        return array_keys($this->sourceRepositories);
    }

    /**
     * Determine if the namespace has the specific source.
     *
     * @param  string  $sourceRepository
     * @return bool
     */
    public function hasSource(string $sourceRepository): bool
    {
        return array_key_exists($sourceRepository, $this->sourceRepositories);
    }

    /**
     * Prepend a source path for the given repository.
     *
     * @param  string|array  $path
     * @param  string  $sourceRepository
     * @return bool
     */
    public function prependPathToSource(string|array $path, string $sourceRepository): bool
    {
        return $this->addPathToSource($path, $sourceRepository, true);
    }

    /**
     * Register a source path for the given repository.
     *
     * @param  string|array  $path
     * @param  string  $sourceRepository
     * @param  bool  $prepend
     * @return bool
     */
    public function addPathToSource(string|array $path, string $sourceRepository, bool $prepend = false): bool
    {
        return $this->getSource($sourceRepository)->addPath($path, $prepend);
    }

    /**
     * Initializes all source repositories for the configured extra sources,
     * to make sure these are included even when nothing was dynamically registered.
     */
    public function initializeConfiguredSources(): void
    {
        foreach (
            $this->getKitsuneHelper()->getPackageSourceConfigurations(
                $this->getName()
            ) as $alias => $configuration
        ) {
            $this->addSource($alias, ...$configuration);
        }
    }

    /**
     * Get a compiled list of paths from the namespace, sources and potentially Laravel's application
     * paths with derivatives for the application layout and the namespace's layout.
     *
     * @param  bool  $includeDefaultPaths  When true, it will add the applications default view paths
     * @return array
     */
    public function getPathsWithDerivatives(bool $includeDefaultPaths = false): array
    {
        $sourcePathDerivatives = [];
        $applicationLayout = $this->getKitsuneCore()->getApplicationLayout();
        $namespaceLayout = $this->getLayout();
        $prioritizedSources = $this->getPaths();

        foreach (
            $this->includeDefaults || $includeDefaultPaths ? $this->getKitsuneHelper()->getLaravelViewPathsByPriority(
            ) : []
            as $priorityValue => $paths
        ) {
            $prioritizedSources[$priorityValue] = array_merge(
                $prioritizedSources[$priorityValue] ?? [],
                $paths
            );
        }

        krsort($prioritizedSources);

        foreach (Arr::flatten($prioritizedSources) as $sourcePath) {
            if ($applicationLayout) {
                $sourcePathDerivatives[] = $sourcePath.DIRECTORY_SEPARATOR.$applicationLayout;
            }

            if ($namespaceLayout) {
                $sourcePathDerivatives[] = $sourcePath.DIRECTORY_SEPARATOR.$namespaceLayout;
            }

            $sourcePathDerivatives[] = $sourcePath;
        }

        return $this->getKitsuneHelper()->filterPaths($sourcePathDerivatives);
    }

    /**
     * Compile a list of possible view source paths sorted by their priority.
     *
     * @return array
     */
    public function getPaths(): array
    {
        if (!empty($this->sourcesByPriority) && !$this->hasUpdates) {
            return $this->sourcesByPriority;
        }

        $prioritizedSources = [$this->getPriority()->getValue() => $this->paths];

        foreach ($this->sourceRepositories as $sourceRepository) {
            $priorityValue = $sourceRepository->getPriority()->getValue();
            $prioritizedSources[$priorityValue] = array_merge(
                $prioritizedSources[$priorityValue] ?? [],
                $sourceRepository->getPaths()
            );
        }

        $this->hasUpdates = false;

        return $this->sourcesByPriority = $prioritizedSources;
    }

    /**
     * Determines the update state and dispatches the event if something changed.
     *
     * As nested resources may propagate their changes by using events or
     * similar, we offer the possibility to pass if something updated.
     *
     * @param  bool  $updated
     * @return bool
     */
    public function dispatchUpdatedEvent(bool $updated = true): bool
    {
        if ($this->hasUpdates |= $updated) {
            KitsuneSourceNamespaceUpdated::dispatch($this);
        }

        return $this->hasUpdates;
    }

}
