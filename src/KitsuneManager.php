<?php

namespace Kitsune\Core;

use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\IsKitsuneManager;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Events\KitsuneManagerInitialized;
use Kitsune\Core\Events\KitsuneSourceNamespaceCreated;

class KitsuneManager implements IsKitsuneManager
{
    use UtilisesKitsune;

    protected bool $initialized = false;
    protected array $namespaces = [];

    /**
     * Retrieve a list of all registered namespaces.
     *
     * @return array
     */
    public function getRegisteredNamespaces(): array
    {
        return array_keys($this->namespaces);
    }

    /**
     * Retrieve the according namespace.
     *
     * @param  string  $namespace
     * @return IsSourceNamespace
     */
    public function getNamespace(string $namespace): IsSourceNamespace
    {
        return $this->namespaces[$namespace];
    }

    /**
     * Determines if a specific Namespace is already registered.
     *
     * @param  string  $namespace
     * @return bool
     */
    public function hasNamespace(string $namespace): bool
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Create a new SourceNamespace using the given configuration or defaults.
     *
     * @param  string  $namespace
     * @param  array  $configuration
     * @return IsSourceNamespace
     */
    public function addNamespace(string $namespace, array $configuration = []): IsSourceNamespace
    {
        $sourceNamespace = $this->namespaces[$namespace] =
            new ($this->getKitsuneHelper()->getSourceNamespaceClass())(
                $namespace,
                ...$this->getKitsuneHelper()->toCamelKeys($configuration)
            );

        KitsuneSourceNamespaceCreated::dispatch($sourceNamespace);

        return $sourceNamespace;
    }

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
    ): IsSourceNamespace {
        $sourceNamespace = $this->addNamespace($namespace, $namespaceConfiguration);

        $sourceNamespace->addPathToSource($namespace, 'published');
        $vendorPaths && $sourceNamespace->addPathToSource($vendorPaths, 'vendor');

        return $this->namespaces[$namespace];
    }

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
    public function initialize(): void
    {
        if (!$this->initialized) {
            $kitsune = $this->getKitsuneCore();
            $initialRefreshState = $kitsune->shouldAutoRefresh();

            $initialRefreshState && $kitsune->disableAutoRefresh();

            $this->initializePackages();
            $this->initializeNamespaces();

            $initialRefreshState && $kitsune->enableAutoRefresh();

            $this->initialized = true;

            KitsuneManagerInitialized::dispatch($this);
        }
    }

    /**
     * Initialize registered namespaces, if they have not been initialized before.
     *
     * @return void
     */
    public function initializeNamespaces(): void
    {
        foreach (config('kitsune.view.namespaces', []) as $namespace => $configuration) {
            if (is_int($namespace)) {
                $namespace = $configuration;
                $configuration = [];
            }

            if (!$this->hasNamespace($namespace)) {
                $this->addNamespace($namespace, $configuration);
            }
        }
    }

    /**
     * Initialize registered package namespaces and add paths to package namespaces.
     *
     * @return void
     */
    public function initializePackages(): void
    {
        foreach (config('kitsune.packages', []) as $package => $configuration) {
            if (!$this->hasNamespace($package)) {
                $this->addPackage($package, null, $configuration['namespace'] ?? []);
            }

            $sourceNamespace = $this->getNamespace($package);

            foreach ($configuration['paths'] ?? [] as $source => $paths) {
                $sourceNamespace->addPathToSource($paths, $source);
            }
        }
    }
}
