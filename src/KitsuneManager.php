<?php

namespace Shiriso\Kitsune\Core;

use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneManager;

class KitsuneManager implements IsKitsuneManager
{
    use UtilisesKitsune;

    protected ?string $globalNamespace;
    protected ?string $globalLayout;
    protected array $namespaces = [];

    public function __construct()
    {
        $this->globalNamespace = config('kitsune.core.global_mode.namespace');
        $this->globalLayout = config('kitsune.view.layout');

        $this->initializeNamespaces();
        //$this->initializePackages();
    }

    /**
     * Activate the global mode for the given namespace, and make sure it is disabled for every other namespace.
     *
     * @param  string|null  $namespace
     * @return bool
     */
    public function setGlobalNamespace(?string $namespace): bool
    {
        if ($this->globalNamespace === $namespace) {
            return false;
        }

        $this->globalNamespace = $namespace;

        if (!$namespace) {
            return config('kitsune.core.global_mode.reset_on_disable') && $this->getKitsuneCore()->resetGlobalViewFinder();
        }

        return $this->getKitsuneCore()->configureGlobalViewFinder($namespace);
    }

    /**
     * Get the namespace which is currently configured for global mode.
     *
     * @return string|null
     */
    public function getGlobalNamespace(): ?string
    {
        return $this->globalNamespace;
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
                ...app('kitsune.helper')->toCamelKeys($configuration)
            );
    }

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
    ): IsSourceNamespace {
        $sourceNamespace = $this->addNamespace($namespace, $namespaceConfiguration);

        if (!$sourceNamespace->hasSource('published')) {
            $sourceNamespace->addSource(
                'published',

                ...$this->getKitsuneHelper()->getDefaultSourceConfiguration('published')
            );
        }

        // TODO: ADD PATH TO SOURCE

        return $this->namespaces[$namespace];
    }

    /**
     * Initialize the configured namespaces.
     *
     *
     */
    protected function initializeNamespaces(): void
    {
        foreach (config('kitsune.view.namespaces', []) as $namespace => $configuration) {
            is_int($namespace) ? $this->addNamespace($configuration) : $this->addNamespace($namespace, $configuration);
        }
    }

    protected function initializePackages(): void
    {
        foreach (config('kitsune.packages', []) as $package => $configuration)
        {
            // TODO: IMPLEMENT
            //$this->addPackage($package);
        }
    }
}
