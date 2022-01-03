<?php

namespace Kitsune\Core\Contracts;

interface IsKitsuneCore
{
    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string|IsSourceNamespace $namespace): bool;

    /**
     * Activates Kitsune for the application and configures the necessary services.
     *
     * @return void
     */
    public function initialize(): void;

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
     * Get the layout which is currently configured for the application.
     *
     * @return string|null
     */
    public function getApplicationLayout(): ?string;

    /**
     * Disable the automatic updates of view sources when a namespace has been updated.
     *
     * @return $this
     */
    public function disableAutoRefresh(): static;

    /**
     * Activate the global mode for the given namespace, and make sure it is disabled for every other namespace.
     *
     * If a namespace gets defined as global before existence, Kitsune will skip setting the global paths.
     * When autoRefresh is activated, it will automatically be published once the namespace is set up.
     *
     * @param  IsSourceNamespace|string|null  $namespace
     * @return bool|null Returns null if nothing changed, false if no paths have been updated or true if it did.
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
     * Determine if global mode is activated.
     *
     * @return bool
     */
    public function globalModeEnabled(): bool;

    /**
     * Set the layout for the application.
     *
     * @param  string|null  $layout
     * @return bool
     */
    public function setApplicationLayout(?string $layout): bool;

    /**
     * Enable global mode for Kitsune.
     *
     * @return bool
     */
    public function enableGlobalMode(): bool;
}
