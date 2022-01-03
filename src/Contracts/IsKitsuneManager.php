<?php

namespace Kitsune\Core\Contracts;

interface IsKitsuneManager
{

    /**
     * Determines if a specific Namespace is already registered.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function hasNamespace(string $namespace): bool;

    /**
     * Initializes the configured namespaces.
     *
     * This will temporarily disable the automatic refresh to not trigger
     * it on every new namespace, source or path to be added.
     *
     * If it was configured to automatically refresh paths before
     * initialization it will trigger the refresh when all
     * namespaces and sources have been configured.
     */
    public function initialize(): void;

    /**
     * Initialize registered namespaces, if they have not been initialized before.
     *
     * @return void
     */
    public function initializeNamespaces(): void;

    /**
     * Retrieve the according namespace.
     *
     * @param  string  $namespace
     * @return IsSourceNamespace
     */
    public function getNamespace(string $namespace): IsSourceNamespace;

    /**
     * Initialize registered package namespaces and add paths to package namespaces.
     *
     * @return void
     */
    public function initializePackages(): void;

    /**
     * Create a new SourceNamespace for a package, which will already include the published path for vendor views.
     *
     * @param  string  $namespace
     * @param  string|array|null  $vendorPaths
     * @param  array  $namespaceConfiguration
     * @return IsSourceNamespace
     */
    public function addPackage(
        string $namespace,
        string|array|null $vendorPaths = null,
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
}
