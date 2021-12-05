<?php

namespace Shiriso\Kitsune\Core\Contracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Shiriso\Kitsune\Core\Exceptions\InvalidPriorityDefinitionException;
use Shiriso\Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Shiriso\Kitsune\Core\Exceptions\InvalidSourceRepositoryException;
use Shiriso\Kitsune\Core\KitsuneHelper;

interface IsKitsuneHelper
{

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
     * Retrieves the default priority for a given type.
     *
     * @param  string  $type
     * @return DefinesPriority
     */
    public function getPriorityDefault(string $type): DefinesPriority;

    /**
     * Get the currently configured manager class implementing ProvidesKitsuneManager.
     *
     * @return string
     * @throws InvalidKitsuneManagerException
     */
    public function getManagerClass(): string;

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
     * @return string
     */
    public function priorityDefinitionIsEnum(): string;

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
     * Get the paths registered in Laravel's default config index by the default priority value.
     *
     * @return array
     */
    public function getLaravelViewPathsByPriority(): array;
}