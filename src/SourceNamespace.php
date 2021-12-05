<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;

class SourceNamespace implements IsSourceNamespace
{
    use UtilisesKitsune;

    protected ?KitsuneManager $manager;
    protected array $sourceRepositories = [];
    protected array $sourcesByPriority = [];
    protected bool $hasUpdates = true;

    public function __construct(
        protected string $namespace,
        protected bool $addDefaults = false,
        protected string|DefinesPriority|null $priority = null,
        protected ?string $layout = null,
        protected string|array $paths = []
    ) {
        $this->paths = Arr::wrap($this->paths);

        $this->setPriority($this->priority ?? 'namespace');

        $this->initializeConfiguredSources();
    }

    /**
     * Set a new priority.
     *
     * @param  string|DefinesPriority  $priority
     * @return bool
     */
    public function setPriority(string|DefinesPriority $priority): bool
    {
        if (is_string($priority)) {
            $priority = $this->getKitsuneHelper()->getPriorityDefault($priority);
        }

        if ($this->priority?->getValue() !== $priority->getValue()) {
            $this->priority = $priority;

            return true;
        }

        return false;
    }

    /**
     * Get the priority of the namespace.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority
    {
        return $this->priority;
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
        $this->hasUpdates = $this->hasUpdates || $this->getSource($source)->setPriority($priority);

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
     * @param  string  $layout
     * @return bool
     */
    public function setLayout(string $layout): bool
    {
        if ($this->layout !== $layout) {
            $this->layout = $layout;
            $this->hasUpdates = true;

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
        $this->setUpdateState();

        return $this->sourceRepositories[$sourceRepository] =
            new (app('kitsune.helper')->getSourceRepositoryClass())(...func_get_args());
    }

    /**
     * Get the repository for the alias or create a new one if it does not exist.
     *
     * Implicit creation only works for sources which are defined in the config.
     *
     * @param  string  $sourceRepository
     * @return SourceRepository
     */
    public function getSource(string $sourceRepository): SourceRepository
    {
        return $this->sourceRepositories[$sourceRepository] ??= $this->addSource($sourceRepository);
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
     * @param  string  $path
     * @param  string|array  $sourceRepository
     * @return bool
     */
    public function prependPathToSource(string $path, string|array $sourceRepository): bool
    {
        return $this->addPathToSource($path, $sourceRepository, true);
    }

    /**
     * Register a source path for the given repository.
     *
     * @param  string  $path
     * @param  string|array  $sourceRepository
     * @param  bool  $prepend
     * @return bool
     */
    public function addPathToSource(string $path, string|array $sourceRepository, bool $prepend = false): bool
    {
        $repository = $this->getSource(...Arr::wrap($sourceRepository));

        if ($repository->addPath($path, $prepend)) {
            $this->setUpdateState();

            // TODO: USE KITSUNE
            //return $this->refreshViewSources();

            return true;
        }

        return false;
    }

    /**
     * Initializes all source repositories for the configured extra sources,
     * to make sure these are included even when nothing was dynamically registered.
     */
    protected function initializeConfiguredSources(): void
    {
        foreach ($this->getKitsuneHelper()->getDefaultSourceConfigurations() as $alias => $configuration) {
            $this->addSource($alias, ...$configuration);
        }
    }

    /**
     * Get a compiled list of paths from the namespace, sources and potentially Laravel's application
     * paths with derivatives for the application layout and the namespace's layout.
     *
     * @param  bool  $addDefaultPaths  When true, it will add the applications default paths from Laravel
     * @return array
     */
    public function getPathsWithDerivatives(bool $addDefaultPaths = false): array
    {
        $sourcePathDerivatives = [];
        $applicationLayout = $this->getKitsuneManager()->getApplicationLayout();
        $namespaceLayout = $this->getLayout();
        $prioritizedSources = $this->getPaths();

        foreach (
            $addDefaultPaths ? $this->getKitsuneHelper()->getLaravelViewPathsByPriority() : []
            as $priorityValue => $paths
        ) {
            $prioritizedSources[$priorityValue] = array_merge(
                $prioritizedSources[$priorityValue] ?? [],
                $paths
            );
        }

        krsort($prioritizedSources);

        foreach (Arr::flatten($prioritizedSources) as $sourcePath) {
            if($applicationLayout) {
                $sourcePathDerivatives[] = $sourcePath . DIRECTORY_SEPARATOR . $applicationLayout;
            }

            if($namespaceLayout) {
                $sourcePathDerivatives[] = $sourcePath . DIRECTORY_SEPARATOR . $namespaceLayout;
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
     * Determines the update state and sets it the namespace.
     *
     * As the nested mutators to sources and similar will return
     * if the setter actually changed something, we do not want
     * to set the hasUpdates every time a setter was called,
     * but only when there was an actual change to it.
     *
     * @param  bool  $state
     * @return bool
     */
    protected function setUpdateState(bool $state = true): bool
    {
        return $this->hasUpdates = $this->hasUpdates || $state;
    }
}
