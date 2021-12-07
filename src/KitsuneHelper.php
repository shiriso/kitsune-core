<?php

namespace Shiriso\Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;
use Shiriso\Kitsune\Core\Contracts\DefinesPriority;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneCore;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneHelper;
use Shiriso\Kitsune\Core\Contracts\IsKitsuneManager;
use Shiriso\Kitsune\Core\Contracts\IsSourceNamespace;
use Shiriso\Kitsune\Core\Contracts\IsSourceRepository;
use Shiriso\Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Shiriso\Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Shiriso\Kitsune\Core\Exceptions\InvalidPriorityDefinitionException;
use Shiriso\Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Shiriso\Kitsune\Core\Exceptions\InvalidSourceRepositoryException;
use Shiriso\Kitsune\Core\Exceptions\PriorityDefinitionNotEnumException;

class KitsuneHelper implements IsKitsuneHelper
{
    use UtilisesKitsune;

    protected ?array $defaultSources = null;

    /**
     * Filter given paths based on their existing directory in the filesystem.
     *
     * @param  array  $viewPaths
     * @return array
     */
    public function filterPaths(array $viewPaths): array
    {
        return array_filter($viewPaths, fn($viewPath) => is_dir($viewPath));
    }

    /**
     * Get the currently configured core class implementing ProvidesKitsuneCore.
     *
     * @return string
     * @throws InvalidKitsuneCoreException
     */
    public function getCoreClass(): string
    {
        if (is_a(
            $coreClass = config('kitsune.core.service.class', Kitsune::class),
            IsKitsuneCore::class,
            true
        )) {
            return $coreClass;
        }

        throw new InvalidKitsuneCoreException($coreClass);
    }

    /**
     * Get the currently configured manager class implementing ProvidesKitsuneManager.
     *
     * @return string
     * @throws InvalidKitsuneManagerException
     */
    public function getManagerClass(): string
    {
        if (is_a(
            $managerClass = config('kitsune.core.service.manager', KitsuneManager::class),
            IsKitsuneManager::class,
            true
        )) {
            return $managerClass;
        }

        throw new InvalidKitsuneManagerException($managerClass);
    }

    /**
     * Get the currently configured namespace class implementing IsSourceNamespace.
     *
     * @return string
     * @throws InvalidSourceNamespaceException
     */
    public function getSourceNamespaceClass(): string
    {
        if (is_a(
            $managerClass = config('kitsune.core.service.namespace', SourceNamespace::class),
            IsSourceNamespace::class,
            true
        )) {
            return $managerClass;
        }

        throw new InvalidSourceNamespaceException($managerClass);
    }

    /**
     * Get the currently configured source repository class implementing IsSourceRepository.
     *
     * @return string
     * @throws InvalidSourceRepositoryException
     */
    public function getSourceRepositoryClass(): string
    {
        if (is_a(
            $repositoryClass = config('kitsune.core.service.source', SourceRepository::class),
            IsSourceRepository::class,
            true
        )) {
            return $repositoryClass;
        }

        throw new InvalidSourceRepositoryException($repositoryClass);
    }

    /**
     * Get the currently configured priority definition class or enum.
     *
     * @return string
     * @throws InvalidPriorityDefinitionException
     */
    public function getPriorityDefinition(): string
    {
        if (is_a(
            $priorityClass = config('kitsune.core.priority.definition', KitsunePriority::class),
            DefinesPriority::class,
            true
        )) {
            return $priorityClass;
        }

        throw new InvalidPriorityDefinitionException($priorityClass);
    }

    /**
     * Determine if the currently used priority definition is an enum or a regular class.
     *
     * @return string
     */
    public function priorityDefinitionIsEnum(): string
    {
        return function_exists('enum_exists') && enum_exists($this->getPriorityDefinition());
    }

    /**
     * Retrieves the default priority for a given type.
     *
     * @param  string|DefinesPriority  $type
     * @return DefinesPriority
     */
    public function getPriorityDefault(string|DefinesPriority $type): DefinesPriority
    {
        if (is_a($type, DefinesPriority::class)) {
            return $type;
        }

        if ($priority = config('kitsune.core.priority.defaults.'.$type)) {
            return is_a($priority, DefinesPriority::class) ? $priority : $this->getPriorityDefault($priority);
        }

        $defaultPriority = match ($type) {
            'vendor' => 'low',
            'namespace', 'source' => 'medium',
            'published' => 'high',
            'laravel' => 'important',
            default => $type,
        };

        return $this->priorityDefinitionIsEnum()
            ? $this->getPriorityEnum($defaultPriority)
            : $this->getPriorityObject($defaultPriority);
    }

    /**
     * Get an enum of the current priority for the given case.
     *
     * @param  string  $priority
     * @return DefinesPriority
     * @throws PriorityDefinitionNotEnumException
     */
    protected function getPriorityEnum(string $priority): DefinesPriority
    {
        $priorityDefinition = $this->getPriorityDefinition();

        if (!$this->priorityDefinitionIsEnum()) {
            throw new PriorityDefinitionNotEnumException($priorityDefinition);
        }

        return match ($priority) {
            'least' => $priorityDefinition::LEAST,
            'low' => $priorityDefinition::LOW,
            'medium' => $priorityDefinition::MEDIUM,
            'high' => $priorityDefinition::HIGH,
            'important' => $priorityDefinition::IMPORTANT,
        };
    }

    protected function getPriorityObject(string $priority): DefinesPriority
    {
        return new ($this->getPriorityDefinition())($priority);
    }

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
    public function toCamelKeys(array|Collection $stack, bool $returnAsArray = false): array|Collection
    {
        if ($returnAsArray && !is_array($stack)) {
            $stack = $stack->all();
        }

        return is_array($stack) ? Arr::mapWithKeys(
            $stack,
            fn($value, $key) => [Str::camel($key) => $value]
        ) : $stack->mapWithKeys(fn($value, $key) => [Str::camel($key) => $value]);
    }

    /**
     * Determines if the paths have been updated by comparing them after sorting.
     *
     * @param  array  $newPaths
     * @param  array|null  $oldPaths
     * @return bool
     */
    public function pathsHaveUpdates(array $newPaths, ?array $oldPaths): bool
    {
        sort($newPaths);
        is_array($oldPaths) && sort($oldPaths);

        return $newPaths !== $oldPaths;
    }

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
    public function getDefaultSourceConfigurations(): array
    {
        return $this->defaultSources ??= array_replace_recursive(
            $this->getDefaultSources(),
            array_map([$this, 'toCamelKeys'], config('kitsune.view.sources', []))
        );
    }

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
    public function getPackageSourceConfigurations(string $package): array
    {
        return $this->packageSources[$package] ??= array_replace_recursive(
            $this->getDefaultSourceConfigurations(),
            array_map([$this, 'toCamelKeys'], config(sprintf('kitsune.packages.%s.sources', $package), []))
        );
    }

    /**
     * Returns an array with all existing default source configurations
     * as defined in the KitsuneHelper
     *
     * @return array
     */
    public function getAvailableDefaultSourceConfigurations(): array
    {
        return array_keys($this->getDefaultSourceConfigurations());
    }

    /**
     * Retrieve the default configuration for a specific source.
     *
     * @param  string  $source
     * @return array
     * @throws InvalidDefaultSourceConfiguration
     */
    public function getDefaultSourceConfiguration(string $source): array
    {
        return $this->getDefaultSourceConfigurations()[$source]
            ?? throw new InvalidDefaultSourceConfiguration($source);
    }

    /**
     * Get the paths registered in Laravel's default config index by the default priority value.
     *
     * @return array
     */
    public function getLaravelViewPathsByPriority(): array
    {
        return [$this->getPriorityDefault('laravel')->getValue() => config('view.paths')];
    }

    /**
     * Get the actual source namespace by its name or the namespace itself.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return IsSourceNamespace
     */
    public function getNamespace(string|IsSourceNamespace $namespace): IsSourceNamespace
    {
        return is_a($namespace, IsSourceNamespace::class) ? $namespace : $this->getKitsuneManager()->getNamespace(
            $namespace
        );
    }

    /**
     * Get the actual source namespace alias by its name or the namespace itself.
     *
     * @param  string|IsSourceNamespace  $namespace
     * @return string
     */
    public function getNamespaceAlias(string|IsSourceNamespace $namespace): string
    {
        return is_a($namespace, IsSourceNamespace::class) ? $namespace->getName() : $namespace;
    }

    /**
     * Get the default sources which we expect always to exist for internal usages.
     *
     * @return array
     */
    protected function getDefaultSources(): array
    {
        return [
            'published' => [
                'basePath' => resource_path('views/vendor'),
                'priority' => 'published',
                'paths' => [],
            ],
            'vendor' => [
                'basePath' => base_path('vendor'),
                'priority' => 'vendor',
                'paths' => [],
            ],
        ];
    }
}
