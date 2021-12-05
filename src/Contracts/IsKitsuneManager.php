<?php

namespace Shiriso\Kitsune\Core\Contracts;

interface IsKitsuneManager
{

    /**
     * Get the namespace which is currently configured for global mode.
     *
     * @return string|null
     */
    public function getGlobalNamespace(): ?string;

    /**
     * Activate the global mode for the given namespace, and make sure it is disabled for every other namespace.
     *
     * @param  string|null  $namespace
     * @return bool
     */
    public function setGlobalNamespace(?string $namespace): bool;

    /**
     * Retrieve the according namespace.
     *
     * @param  string  $namespace
     * @return IsSourceNamespace
     */
    public function getNamespace(string $namespace): IsSourceNamespace;

    /**
     * Create a new SourceNamespace for a package, which will already include the published path for vendor views.
     *
     * @param  string  $namespace
     * @param  string|array  $vendorPaths
     * @param  array  $namespaceConfiguration
     * @return IsSourceNamespace
     */
    public function addPackage(
        string $namespace,
        string|array $vendorPaths,
        array $namespaceConfiguration = []
    ): IsSourceNamespace;

    /**
     * Create a new SourceNamespace using the given configuration or defaults.
     *
     * @param  string  $namespace
     * @param  array  $configuration
     * @return IsSourceNamespace
     */
    public function addNamespace(string $namespace, array $configuration = []): IsSourceNamespace;

    /**
     * Retrieve a list of all registered namespaces.
     *
     * @return array
     */
    public function getRegisteredNamespaces(): array;

    /**
     * Get the layout which is currently configured for the application.
     *
     * @return string|null
     */
    public function getApplicationLayout(): ?string;

    /**
     * Set the layout for the application.
     *
     * @param  string|null  $layout
     * @return bool
     */
    public function setApplicationLayout(?string $layout): bool;
}