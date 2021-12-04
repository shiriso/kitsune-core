<?php

namespace Shiriso\Kitsune\Core\Contracts;

interface IsKitsuneCore
{

    /**
     * Configures the view sources accordingly to Kitsune.
     *
     * @return bool
     */
    public function refreshViewSources(): bool;

    /**
     * Configure the global ViewFinder-Paths accordingly to the given namespace.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function configureGlobalViewFinder(string $namespace): bool;

    /**
     * Resets the global ViewFinder-Paths to the application's default.
     *
     * @return bool
     */
    public function resetGlobalViewFinder(): bool;

    public function configureNamespaceViewFinder(string $namespace): bool;
}