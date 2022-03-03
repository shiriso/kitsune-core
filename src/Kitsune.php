<?php

namespace Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsKitsuneCore;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Events\KitsuneCoreInitialized;
use Kitsune\Core\Events\KitsuneCoreUpdated;

class Kitsune implements IsKitsuneCore
{
    use UtilisesKitsune;

    protected bool $autoRefresh = false;
    protected bool $autoInitialize = false;
    protected bool $globalModeEnabled = false;
    protected bool $initialized = false;
    protected ?string $applicationLayout = null;
    protected ?string $globalNamespace = null;
    protected FileViewFinder $viewFinder;

    public function __construct()
    {
        $this->viewFinder = View::getFinder();
        $this->setGlobalModeEnabled(config('kitsune.core.global_mode.enabled', false));
        $this->setGlobalNamespace(config('kitsune.core.global_mode.namespace'));
        $this->setAutoRefresh(config('kitsune.core.auto_refresh', true));
        $this->setAutoInitialize(config('kitsune.core.auto_initialize', true));
        $this->setApplicationLayout(config('kitsune.view.layout'));
    }

    /**
     * Activates Kitsune for the application and configures the necessary services.
     *
     * @return void
     */
    public function initialize(): void
    {
        if (!$this->initialized) {
            $this->getKitsuneManager()->initialize();

            $this->initialized = true;

            KitsuneCoreInitialized::dispatch($this);
        }
    }

    /**
     * Checks if the Core has already been initialized.
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Enable global mode for Kitsune.
     *
     * @return bool
     */
    public function enableGlobalMode(): bool
    {
        return $this->setGlobalModeEnabled(true);
    }

    /**
     * Disable global mode for Kitsune.
     *
     * @return bool
     */
    public function disableGlobalMode(): bool
    {
        return $this->setGlobalModeEnabled(false);
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
     * Set if the global mode is enabled or not.
     *
     * @param  bool  $enabled
     * @return bool
     */
    protected function setGlobalModeEnabled(bool $enabled): bool
    {
        if ($this->globalModeEnabled() !== $enabled) {
            $this->globalModeEnabled = $enabled;

            $this->dispatchCoreUpdatedEvent();

            return true;
        }

        return false;
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
            return $this->shouldResetOnDisableGlobalMode() && $this->resetGlobalViewFinder();
        }

        if ($registeredNamespace = $this->getGlobalNamespace()) {
            return $registeredNamespace->dispatchUpdatedEvent(true);
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
        return $this->globalModeEnabled() && $this->globalNamespace && $this->getKitsuneManager()->hasNamespace($this->globalNamespace)
            ? $this->getKitsuneManager()->getNamespace($this->globalNamespace)
            : null;
    }

    /**
     * Get the layout which is currently configured for the application.
     *
     * @return string|null
     */
    public function getApplicationLayout(): ?string
    {
        return $this->applicationLayout;
    }

    /**
     * Set the layout for the application.
     *
     * @param  string|null  $layout
     * @return bool
     */
    public function setApplicationLayout(?string $layout): bool
    {
        if ($this->applicationLayout !== $layout) {
            $this->applicationLayout = $layout;

            $this->dispatchCoreUpdatedEvent();

            return true;
        }

        return false;
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
     * Define if Kitsune is supposed to automatically be initialized during the apps boot process,
     * or if it will only be programmatically be activated during runtime
     *
     * @param  bool  $autoInitialize
     * @return $this
     */
    protected function setAutoInitialize(bool $autoInitialize): static
    {
        $this->autoInitialize = $autoInitialize;

        return $this;
    }

    /**
     * Determines if Kitsune should automatically propagate changes to the namespace.
     *
     * @return bool
     */
    public function shouldAutoInitialize(): bool
    {
        return $this->autoInitialize;
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

        !$this->getGlobalNamespace() && $this->shouldResetOnDisableGlobalMode() && $this->resetGlobalViewFinder();

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
     * Retrieve the registered global view paths.
     *
     * @return array
     */
    public function getViewPaths(): array
    {
        return $this->viewFinder->getPaths();
    }

    /**
     * Retrieve the view paths registered for a namespace.
     *
     * @param  IsSourceNamespace  $namespace
     * @return array|null
     */
    public function getViewNamespacePaths(IsSourceNamespace $namespace): ?array
    {
        return $this->viewFinder->getHints()[$namespace->getName()] ?? null;
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
            $this->getViewPaths()
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
            $this->getViewNamespacePaths($namespace)
        );
    }

    /**
     * Dispatches the core updated event if the core has been initialized before.
     *
     * @return void
     */
    protected function dispatchCoreUpdatedEvent(): void
    {
        KitsuneCoreUpdated::dispatchIf($this->initialized, $this);
    }

    /**
     * Checks if the global view finder paths are supposed to be reset when disabling the global mode.
     *
     * @return bool
     */
    protected function shouldResetOnDisableGlobalMode(): bool
    {
        return config('kitsune.core.global_mode.reset_on_disable', true);
    }
}
