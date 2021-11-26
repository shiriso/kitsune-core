<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Traits\Tappable;
use Illuminate\View\ViewFinderInterface;

class Kitsune
{
    use Tappable;

    protected ?string $activeLayout;
    protected array $extraSourceRepositories = [];
    protected ViewFinderInterface $viewFinder;

    public function __construct()
    {
        $this->viewFinder = View::getFinder();

        $this->initializeConfiguredExtraSources();
    }

    /**
     * @return bool
     */
    public function refreshViewSources(): bool
    {
        $defaultViewPaths = Arr::wrap(config('view.paths'));

        return $this->setViewFinderPaths($this->compileViewPathDerivatives($defaultViewPaths));
    }

    /**
     * Set a new active layout.
     *
     * @param  string  $layout
     */
    public function setActiveLayout(string $layout): void
    {
        $this->activeLayout = $layout;

        $this->refreshViewSources();
    }

    /**
     * Get the currently activated layout.
     *
     * @return string|null
     */
    public function getActiveLayout(): ?string
    {
        return $this->activeLayout ??= config('kitsune.view.layout');
    }

    /**
     * Get the repository for the alias or create a new one if it does not exist.
     *
     * @param  string  $sourceRepository
     * @param  array|null  $sourcePaths
     * @param  string|null  $basePath
     * @return SourcePathRepository
     */
    public function getSourceRepository(
        string $sourceRepository,
        array $sourcePaths = null,
        string $basePath = null
    ): SourcePathRepository {
        return $this->extraSourceRepositories[$sourceRepository] ??= new class(...func_get_args()) extends
            SourcePathRepository {
        };
    }

    /**
     * Prepend a source path for the given repository.
     *
     * @param  string  $sourcePath
     * @param  string|array  $sourceRepository
     * @return bool
     */
    public function prependPathForSource(string $sourcePath, string|array $sourceRepository): bool
    {
        return $this->addPathForSource($sourcePath, $sourceRepository, true);
    }

    /**
     * Register a source path for the given repository.
     *
     * @param  string  $sourcePath
     * @param  string|array  $sourceRepository
     * @param  bool  $prepend
     * @return bool
     */
    public function addPathForSource(string $sourcePath, string|array $sourceRepository, bool $prepend = false): bool
    {
        $repository = call_user_func_array([$this, 'getSourceRepository'], Arr::wrap($sourceRepository));

        if ($prepend ? $repository->prependSource($sourcePath) : $repository->addSource($sourcePath)) {
            return $this->refreshViewSources();
        }

        return false;
    }

    /**
     * Get the source paths which have been registered for the source repository.
     *
     * @param  string  $sourceRepository
     * @return array
     */
    public function getRegisteredSourcePaths(string $sourceRepository): array
    {
        return $this->getSourceRepository($sourceRepository)->getSourcePaths();
    }

    /**
     * Configure the "ViewFinder" to resolve
     *
     * @param  array  $viewPaths
     * @return bool
     */
    protected function setViewFinderPaths(array $viewPaths): bool
    {
        $availablePaths = $this->filterPaths($viewPaths);

        // Only update paths if something changed, otherwise there is no need to flush already resolved views.
        if (array_diff($availablePaths, $this->viewFinder->getPaths())) {
            // Flush resolved views, as changing the paths for the finder, may also change the view which gets resolved.
            $this->viewFinder->setPaths($availablePaths)->flush();

            return true;
        }

        return false;
    }

    /**
     * Filter given paths based on their existing directory in the filesystem.
     *
     * @param  array  $viewPaths
     * @return array
     */
    protected function filterPaths(array $viewPaths): array
    {
        return array_filter($viewPaths, fn($viewPath) => is_dir($viewPath));
    }

    /**
     * Compile a list of possible source paths based on
     *
     * @param  array  $sourcePaths
     * @return array
     */
    protected function compileViewPathDerivatives(array $sourcePaths): array
    {
        $viewPaths = [];
        $activeLayout = $this->getActiveLayout();

        if ($activeLayout) {
            foreach ($sourcePaths as $sourcePath) {
                $viewPaths[] = sprintf('%s/%s', $sourcePath, $this->getActiveLayout());
            }
        }

        foreach ($this->extraSourceRepositories as $sourceRepository) {
            foreach ($sourceRepository->getSourcePaths() as $registeredVendorPath) {
                if ($activeLayout) {
                    $viewPaths[] = sprintf('%s/%s', $registeredVendorPath, $this->getActiveLayout());
                }

                $viewPaths[] = $registeredVendorPath;
            }
        }

        return array_merge($viewPaths, $sourcePaths);
    }

    /**
     * Initializes all source repositories for the configured extra sources,
     * to make sure these are included even when nothing was dynamically registered.
     */
    protected function initializeConfiguredExtraSources(): void
    {
        foreach (array_keys(config('kitsune.view.extra_sources', [])) as $alias) {
            $this->getSourceRepository($alias);
        }
    }
}