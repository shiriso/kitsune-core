<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneCore;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;

class Kitsune implements IsKitsuneCore
{
    use UtilisesKitsune;

    protected bool $autoRefresh = false;
    protected bool $globalModeEnabled = false;
    protected ?string $globalNamespace = null;
    protected FileViewFinder $viewFinder;
    protected ?string $layout;

    public function __construct()
    {
        $this->viewFinder = View::getFinder();
        $this->layout = config('kitsune.view.layout');
        $this->globalModeEnabled = config('kitsune.core.global_mode.enabled', false);
        $this->setGlobalNamespace(config('kitsune.core.global_mode.namespace'));
        $this->setAutoRefresh(config('kitsune.core.auto_refresh', true));
    }

    /**
     * Activates Kitsune for the application and configures the necessary services.
     *
     * @return void
     */
    public function start(): void
    {
        $this->getKitsuneManager()->initialize();
    }

    /**
     * Enable global mode for Kitsune.
     *
     * @return bool
     */
    public function enableGlobalMode(): bool
    {
        if (!$this->globalModeEnabled()) {
            return $this->globalModeEnabled = true;
        }

        return false;
    }

    /**
     * Disable global mode for Kitsune.
     *
     * @return bool
     */
    public function disableGlobalMode(): bool
    {
        if ($this->globalModeEnabled()) {
            $this->globalModeEnabled = false;

            return true;
        }

        return false;
    }

    /**
     * Determine if global mode is activated.
     *
     * @return bool
     */
    public function globalModeEnabled(): bool
    {
        return $this->globalModeEnabled;
    }

    /**
     * Activate the global mode for the given namespace, and make sure it is disabled for every other namespace.
     *
     * If a namespace gets defined as global before existence, Kitsune will skip setting the global paths.
     * When autoRefresh is activated, it will automatically be published once the namespace is set up.
     *
     * @param  IsSourceNamespace|string|null  $namespace
     * @return bool|null Returns null if nothing changed, false if no paths have been updated or true if it did.
     */
    public function setGlobalNamespace(IsSourceNamespace|string|null $namespace): ?bool
    {
        $namespaceAlias = $namespace ? $this->getKitsuneHelper()->getNamespaceAlias($namespace) : null;

        if ($this->globalNamespace === $namespaceAlias) {
            return null;
        }

        $this->globalNamespace = $namespaceAlias;

        if (!$namespaceAlias) {
            return config('kitsune.core.global_mode.reset_on_disable') && $this->resetGlobalViewFinder();
        }

        if ($registeredNamespace = $this->getGlobalNamespace()) {
            return $registeredNamespace->setUpdateState();
        }

        return null;
    }

    /**
     * Get the namespace which is currently configured for global mode.
     *
     * @return IsSourceNamespace|null
     */
    public function getGlobalNamespace(): ?IsSourceNamespace
    {
        return $this->getKitsuneManager()->hasNamespace($this->globalNamespace)
            ? $this->getKitsuneManager()->getNamespace($this->globalNamespace)
            : null;
    }

    /**
     * Set if the manager is supposed to automatically trigger updates to the ViewFinder
     * once a namespace has received updates in its configuration or registered paths.
     *
     * @param  bool  $autoRefresh
     * @return $this
     */
    public function setAutoRefresh(bool $autoRefresh): static
    {
        $this->autoRefresh = $autoRefresh;

        return $this;
    }

    /**
     * Enable the automatic updates of view sources when a namespace has been updated.
     *
     * @return $this
     */
    public function enableAutoRefresh(): static
    {
        return $this->setAutoRefresh(true);
    }

    /**
     * Disable the automatic updates of view sources when a namespace has been updated.
     *
     * @return $this
     */
    public function disableAutoRefresh(): static
    {
        return $this->setAutoRefresh(false);
    }

    /**
     * Determines if Kitsune should automatically propagate changes to the namespace.
     *
     * @return bool
     */
    public function shouldAutoRefresh(): bool
    {
        return $this->autoRefresh;
    }

    /**
     * Configures the view sources accordingly to Kitsune.
     *
     * @return bool
     */
    public function refreshViewSources(): bool
    {
        $manager = $this->getKitsuneManager();
        $updatedSources = false;

        foreach ($manager->getRegisteredNamespaces() as $namespaceAlias) {
            $updatedSources = $this->refreshNamespacePaths($manager->getNamespace($namespaceAlias)) || $updatedSources;
        }

        return $updatedSources;
    }

    /**
     * Configures the view sources for the given namespace accordingly to Kitsune.
     *
     * @param  IsSourceNamespace  $namespace
     * @return bool
     */
    public function refreshNamespacePaths(IsSourceNamespace $namespace): bool
    {
        $updatedGlobalPaths = $this->globalModeEnabled()
            && $this->getGlobalNamespace()->getName() === $namespace->getName()
            && $this->configureGlobalViewFinder($namespace);

        return $this->configureNamespaceViewFinder($namespace) || $updatedGlobalPaths;
    }

    /**
     * Resets the global ViewFinder-Paths to the application's default.
     *
     * @return bool
     */
    public function resetGlobalViewFinder(): bool
    {
        $laravelPaths = Arr::wrap(config('view.paths'));

        if (Arr::wrap($this->viewFinder->getPaths()) !== $laravelPaths) {
            $this->viewFinder->setPaths($laravelPaths)->flush();

            return true;
        }

        return false;
    }

    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string|IsSourceNamespace $namespace): bool
    {
        $namespace = $this->getKitsuneHelper()->getNamespace($namespace);

        if ($this->globalPathsHaveUpdated($namespace)) {
            // Update the global view source paths which are used on a global scale and flush resolved views,
            // as changing the paths for the finder, may also change the view which gets resolved
            // for a specific view name.
            $this->viewFinder->setPaths($namespace->getPathsWithDerivatives(true))->flush();

            return true;
        }

        return false;
    }

    /**
     * Configure the "ViewFinder" with the packages namespace and the resolvable source paths.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return bool
     */
    public function configureNamespaceViewFinder(string|IsSourceNamespace $namespace): bool
    {
        $namespace = $this->getKitsuneHelper()->getNamespace($namespace);

        if ($this->namespacePathsHaveUpdated($namespace)) {
            // Set the view source paths for the package namespace.
            $this->viewFinder->replaceNamespace($namespace->getName(), $namespace->getPathsWithDerivatives());

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
     * @param  IsSourceNamespace  $namespace
     * @return bool
     */
    protected function globalPathsHaveUpdated(IsSourceNamespace $namespace): bool
    {
        return $this->getKitsuneHelper()->pathsHaveUpdates(
            $namespace->getPathsWithDerivatives(true),
            $this->viewFinder->getPaths()
        );
    }

    /**
     * Determines if a namespaces paths have been updated compared to the new paths.
     *
     * @param  IsSourceNamespace  $namespace
     * @return bool
     */
    protected function namespacePathsHaveUpdated(IsSourceNamespace $namespace): bool
    {
        return $this->getKitsuneHelper()->pathsHaveUpdates(
            $namespace->getPathsWithDerivatives(),
            $this->viewFinder->getHints()[$namespace->getName()] ?? null
        );
    }
}
