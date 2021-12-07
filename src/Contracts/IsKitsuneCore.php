<?php

namespace Shiriso\Kitsune\Core\Contracts;

use Shiriso\Kitsune\Core\Kitsune;

interface IsKitsuneCore
{
    /**
     * Activates Kitsune for the application and configures the necessary services.
     *
     * @return void
     */
    public function start(): void;

    /**
     * Configures the view sources accordingly to Kitsune.
     *
     * @return bool
     */
    public function refreshViewSources(): bool;

    /**
     * Get the namespace which is currently configured for global mode.
     *
     * @return IsSourceNamespace|null
     */
    public function getGlobalNamespace(): ?IsSourceNamespace;

    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string|IsSourceNamespace $namespace): bool;

    /**
     * Disable the automatic updates of view sources when a namespace has been updated.
     *
     * @return $this
     */
    public function disableAutoRefresh(): static;

    /**
     * Activate the global mode for the given namespace, and make sure it is disabled for every other namespace.
     *
     * @param  IsSourceNamespace|string|null  $namespace
     * @return bool|null
     */
    public function setGlobalNamespace(IsSourceNamespace|string|null $namespace): ?bool;

    /**
     * Determines if Kitsune should automatically propagate changes to the namespace.
     *
     * @return bool
     */
    public function shouldAutoRefresh(): bool;

    /**
     * Resets the global ViewFinder-Paths to the application's default.
     *
     * @return bool
     */
    public function resetGlobalViewFinder(): bool;

    /**
     * Enable the automatic updates of view sources when a namespace has been updated.
     *
     * @return $this
     */
    public function enableAutoRefresh(): static;

    /**
     * Configures the view sources for the given namespace accordingly to Kitsune.
     *
     * @param  IsSourceNamespace  $namespace
     * @return bool
     */
    public function refreshNamespacePaths(IsSourceNamespace $namespace): bool;

    /**
     * Configure the "ViewFinder" with the packages namespace and the resolvable source paths.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return bool
     */
    public function configureNamespaceViewFinder(string|IsSourceNamespace $namespace): bool;

    /**
     * Disable global mode for Kitsune.
     *
     * @return bool
     */
    public function disableGlobalMode(): bool;

    /**
     * Set if the manager is supposed to automatically trigger updates to the ViewFinder
     * once a namespace has received updates in its configuration or registered paths.
     *
     * @param  bool  $autoRefresh
     * @return $this
     */
    public function setAutoRefresh(bool $autoRefresh): static;

    /**
     * Enable global mode for Kitsune.
     *
     * @return bool
     */
    public function enableGlobalMode(): bool;

    /**
     * Determine if global mode is activated.
     *
     * @return bool
     */
    public function globalModeEnabled(): bool;
}
