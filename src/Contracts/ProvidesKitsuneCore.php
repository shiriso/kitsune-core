<?php

namespace Shiriso\Kitsune\Core\Contracts;

interface ProvidesKitsuneCore
{
    /**
     * Refreshes all Namespaces currently defined in the NamespaceManager.
     *
     * @return bool
     */
    public function refreshViewSources(): bool;

    /**
     * Resets the global ViewFinder-Paths to the application's default.
     *
     * @return bool
     */
    public function resetGlobalViewFinder(): bool;

    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string $namespace): bool;

    /**
     * Configure the sources to the ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function configureNamespaceViewFinder(string $namespace): bool;
}