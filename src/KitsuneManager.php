<?php

namespace Shiriso\Kitsune\Core;

use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneManager;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;

class KitsuneManager implements IsKitsuneManager
{
    use UtilisesKitsune;

    protected ?string $applicationLayout = null;
    protected array $namespaces = [];

    public function __construct()
    {
        $this->setApplicationLayout(config('kitsune.view.layout'));
    }

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
        return $this->namespaces[$namespace] ?? $this->addNamespace(...func_get_args());
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

            foreach ($this->getRegisteredNamespaces() as $namespace) {
                $this->getNamespace($namespace)->setUpdateState();
            }

            return true;
        }

        return false;
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
        return $this->namespaces[$namespace] =
            new ($this->getKitsuneHelper()->getSourceNamespaceClass())(
                $namespace,
                ...$this->getKitsuneHelper()->toCamelKeys($configuration)
            );
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
        $kitsune = $this->getKitsuneCore();
        $initialRefreshState = $kitsune->shouldAutoRefresh();

        $kitsune->disableAutoRefresh();

        $this->initializePackages();
        $this->initializeNamespaces();

        if ($initialRefreshState) {
            $kitsune->enableAutoRefresh();

            foreach ($this->getRegisteredNamespaces() as $namespace) {
                $this->getNamespace($namespace)->setUpdateState();
            }
        }
    }

    /**
     * Initialize registered namespaces, if they have not been initialized before.
     *
     * @return void
     */
    public function initializeNamespaces(): void
    {
        foreach (array_merge(config('kitsune.view.namespaces', []), ['kitsune']) as $namespace => $configuration) {
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
