<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneCore;

class Kitsune implements ProvidesKitsuneCore
{
    use UtilisesKitsune;

    protected array $extraSourceRepositories = [];
    protected FileViewFinder $viewFinder;
    protected ?string $layout;

    public function __construct()
    {
        $this->viewFinder = View::getFinder();
        $this->layout = config('kitsune.view.layout');
    }

    /**
     * Configures the view sources accordingly to Kitsune.
     *
     * @return bool
     */
    public function refreshViewSources(): bool
    {
        return false;

        // TODO: USE SOURCE NAMESPACE
        $derivedSourcePaths = $this->compileViewPathDerivatives(Arr::wrap(config('view.paths')));

        $updatedGlobalPaths = $this->globalModeIsEnabled() && $this->setViewFinderPaths($derivedSourcePaths);

        return $this->setViewFinderNamespacedPaths($derivedSourcePaths) || $updatedGlobalPaths;
    }

    /**
     * Resets the global ViewFinder-Paths to the application's default.
     *
     * @return bool
     */
    public function resetGlobalViewFinder(): bool
    {
        return $this->setViewFinderPaths(Arr::wrap(config('view.paths')));
    }

    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string $namespace): bool
    {
        return false;
        // TODO: CONFIGURE FROM GLOBAL NAMESPACE
        //return $this->setViewFinderPaths($this->getKitsuneManager()->getNamespace($namespace)->getS);
    }

    public function configureNamespaceViewFinder(string $namespace): bool
    {
        // TODO: CONFIGURE NAMESPACE VIEWS
    }

    /**
     * Configure the "ViewFinder" to resolve every view with Kitsune's dynamically registered source paths.
     *
     * @param  array  $viewSourcePaths
     * @return bool
     */
    protected function setViewFinderPaths(array $viewSourcePaths): bool
    {
        if ($this->globalPathsHaveUpdated($viewSourcePaths)) {
            // Update the global view source paths which are used on a global scale and flush resolved views,
            // as changing the paths for the finder, may also change the view which gets resolved
            // for a specific view name.
            $this->viewFinder->setPaths($viewSourcePaths)->flush();

            return true;
        }

        return false;
    }

    /**
     * Configure the "ViewFinder" with the packages namespace and the resolvable source paths.
     *
     * @param  string  $namespace
     * @param  array  $viewSourcePaths
     * @return bool
     */
    protected function setViewFinderNamespacedPaths(string $namespace, array $viewSourcePaths): bool
    {
        if ($this->namespacePathsHaveUpdated($namespace, $viewSourcePaths)) {
            // Set the view source paths for the package namespace.
            $this->viewFinder->replaceNamespace($namespace, $viewSourcePaths);

            // Flush resolved views, as changing the paths for the finder,
            // may also change the view which gets resolved for a specific view name.
            $this->viewFinder->flush();

            return true;
        }

        return false;
    }

    /**
     * Determines if the global paths have been updated compared to the new paths.
     *
     * @param $newPaths
     * @return bool
     */
    protected function globalPathsHaveUpdated($newPaths): bool
    {
        return $this->getKitsuneHelper()->pathsHaveUpdates($newPaths, $this->viewFinder->getPaths());
    }

    /**
     * Determines if a namespaces paths have been updated compared to the new paths.
     *
     * @param $namespace
     * @param $newPaths
     * @return bool
     */
    protected function namespacePathsHaveUpdated($namespace, $newPaths): bool
    {
        return $this->getKitsuneHelper()->pathsHaveUpdates($newPaths, $this->viewFinder->getHints()[$namespace] ?? null);
    }
}