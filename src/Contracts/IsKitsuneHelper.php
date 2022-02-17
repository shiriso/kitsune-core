<?php

namespace Kitsune\Core\Contracts;

use Illuminate\Support\Collection;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Kitsune\Core\Exceptions\InvalidPriorityDefinitionException;
use Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Kitsune\Core\Exceptions\InvalidSourceRepositoryException;

interface IsKitsuneHelper
{
    /**
     * Get the actual source namespace alias by its name or the namespace itself.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return string
     */
    public function getNamespaceAlias(string|IsSourceNamespace $namespace): string;

    /**
     * Retrieve the default configuration for a specific source.
     *
     * @param  string  $source
     * @return array
     * @throws InvalidDefaultSourceConfiguration
     */
    public function getDefaultSourceConfiguration(string $source): array;

    /**
     * Transforms all keys in the array or collection to camel case.
     *
     * This can be used to transform an associative array from the
     * config to be passed in method calls as named parameters.
     *
     * @param  array|Collection  $stack
     * @param  bool  $returnAsArray
     * @return array|Collection
     */
    public function toCamelKeys(array|Collection $stack, bool $returnAsArray = false): array|Collection;

    /**
     * Get the currently configured priority definition class or enum.
     *
     * @return string
     * @throws InvalidPriorityDefinitionException
     */
    public function getPriorityDefinition(): string;

    /**
     * Returns a combined list of configured default sources and sources
     * which Kitsune uses on an internal matter to provide basic
     * functionalities usually required by most packages.
     *
     * In case you need to modify one these values, the most
     * convenient way might be, publishing the config files
     * and adjust the specific keys in there or add new
     * sources which are required for your application.
     *
     * @return array
     */
    public function getDefaultSourceConfigurations(): array;

    /**
     * Get the currently configured source repository class implementing IsSourceRepository.
     *
     * @return string
     * @throws InvalidSourceRepositoryException
     */
    public function getSourceRepositoryClass(): string;

    /**
     * Filter given paths based on their existing directory in the filesystem.
     *
     * @param  array  $viewPaths
     * @return array
     */
    public function filterPaths(array $viewPaths): array;

    /**
     * Get the currently configured namespace class implementing IsSourceNamespace.
     *
     * @return string
     * @throws InvalidSourceNamespaceException
     */
    public function getSourceNamespaceClass(): string;

    /**
     * Returns the combined list of configured default sources
     * and the sources configured for the package namespace.
     *
     * In case you need to modify one these values, the most
     * convenient way might be, publishing the config files
     * and adjust the specific keys in there or add new
     * sources which are required for your application.
     *
     * @param  string  $package
     * @return array
     */
    public function getPackageSourceConfigurations(string $package): array;

    /**
     * Get the actual source namespace by its name or the namespace itself.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function getNamespace(string|IsSourceNamespace $namespace): IsSourceNamespace;

    /**
     * Retrieves the default priority for a given type.
     *
     * @param  string|DefinesPriority  $type
     * @return DefinesPriority
     */
    public function getPriorityDefault(string|DefinesPriority $type): DefinesPriority;

    /**
     * Get the currently configured manager class implementing ProvidesKitsuneManager.
     *
     * @return string
     * @throws InvalidKitsuneManagerException
     */
    public function getManagerClass(): string;

    /**
     * Get the paths registered in Laravel's default config index by the default priority value.
     *
     * @return array
     */
    public function getLaravelViewPathsByPriority(): array;

    /**
     * Returns an array with all existing default source configurations
     * as defined in the KitsuneHelper
     *
     * @return array
     */
    public function getAvailableDefaultSourceConfigurations(): array;

    /**
     * Determine if the currently used priority definition is an enum or a regular class.
     *
     * @return bool
     */
    public function priorityDefinitionIsEnum(): bool;

    /**
     * Get the currently configured core class implementing ProvidesKitsuneCore.
     *
     * @return string
     * @throws InvalidKitsuneCoreException
     */
    public function getCoreClass(): string;

    /**
     * Determines if the paths have been updated by comparing them after sorting.
     *
     * @param  array  $newPaths
     * @param  array|null  $oldPaths
     * @return bool
     */
    public function pathsHaveUpdates(array $newPaths, ?array $oldPaths): bool;

    /**
     * @param  ImplementsPriority  $object
     * @return string
     */
    public function getDefaultIdentifier(ImplementsPriority $object): string;
}
