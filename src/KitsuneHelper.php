<?php

namespace Kitsune\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kitsune\Core\Concerns\UtilisesKitsune;
use Kitsune\Core\Contracts\DefinesClassPriority;
use Kitsune\Core\Contracts\DefinesEnumPriority;
use Kitsune\Core\Contracts\DefinesPriority;
use Kitsune\Core\Contracts\ImplementsPriority;
use Kitsune\Core\Contracts\IsKitsuneCore;
use Kitsune\Core\Contracts\IsKitsuneHelper;
use Kitsune\Core\Contracts\IsKitsuneManager;
use Kitsune\Core\Contracts\IsSourceNamespace;
use Kitsune\Core\Contracts\IsSourceRepository;
use Kitsune\Core\Exceptions\InvalidDefaultSourceConfiguration;
use Kitsune\Core\Exceptions\InvalidDefinesPriorityInterfaceUsage;
use Kitsune\Core\Exceptions\InvalidKitsuneCoreException;
use Kitsune\Core\Exceptions\InvalidKitsuneManagerException;
use Kitsune\Core\Exceptions\InvalidPriorityDefinitionException;
use Kitsune\Core\Exceptions\InvalidPriorityException;
use Kitsune\Core\Exceptions\InvalidSourceNamespaceException;
use Kitsune\Core\Exceptions\InvalidSourceRepositoryException;
use Kitsune\Core\Exceptions\PriorityDefinitionNotEnumException;

class KitsuneHelper implements IsKitsuneHelper
{
    use UtilisesKitsune;

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
            $namespaceClass = config('kitsune.core.service.namespace', SourceNamespace::class),
            IsSourceNamespace::class,
            true
        )) {
            return $namespaceClass;
        }

        throw new InvalidSourceNamespaceException($namespaceClass);
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
     * @throws InvalidDefinesPriorityInterfaceUsage
     */
    public function getPriorityDefinition(): string
    {
        if (is_a(
            $priorityDefinition = config('kitsune.core.priority.definition', KitsunePriority::class),
            DefinesPriority::class,
            true
        )) {
            if (!is_a($priorityDefinition, DefinesEnumPriority::class, true)
                && !is_a($priorityDefinition, DefinesClassPriority::class, true)) {
                throw new InvalidDefinesPriorityInterfaceUsage($priorityDefinition);
            }

            return $priorityDefinition;
        }

        throw new InvalidPriorityDefinitionException($priorityDefinition);
    }

    /**
     * Determine if the currently used priority definition is an enum or a regular class.
     *
     * @return bool
     */
    public function priorityDefinitionIsEnum(): bool
    {
        return function_exists('enum_exists') && enum_exists($this->getPriorityDefinition());
    }

    /**
     * Retrieves the default priority for a given type.
     *
     * @param  string|DefinesPriority|ImplementsPriority  $type
     * @return DefinesPriority
     */
    public function getPriorityDefault(string|DefinesPriority|ImplementsPriority $type): DefinesPriority
    {
        if (is_a($type, DefinesPriority::class)) {
            return $type;
        } elseif (is_a($type, ImplementsPriority::class)) {
            $type = $this->getDefaultIdentifier($type);
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
     * @param  ImplementsPriority  $object
     * @return string
     */
    public function getDefaultIdentifier(ImplementsPriority $object): string
    {
        $type = is_a($object, IsSourceNamespace::class, true) ? 'namespace' :
            (is_a($object, IsSourceRepository::class, true) ? 'source' : null);

        return match ($type) {
            'namespace' => 'namespace',
            'source' => match ($object->getName()) {
                'published', 'vendor' => $object->getName(),
                default => 'source'
            },
            default => 'medium'
        };
    }

    /**
     * Get an enum of the current priority for the given case.
     *
     * @param  string  $priority
     * @return DefinesPriority
     * @throws PriorityDefinitionNotEnumException
     * @throws InvalidPriorityException
     * @throws InvalidDefinesPriorityInterfaceUsage
     */
    protected function getPriorityEnum(string $priority): DefinesPriority
    {
        $priorityDefinition = $this->getPriorityDefinition();

        if (!$this->priorityDefinitionIsEnum()) {
            throw new PriorityDefinitionNotEnumException($priorityDefinition);
        }

        return $priorityDefinition::fromName($priority);
    }

    /**
     * Get the object reflecting a priority based on a class.
     *
     * @param  string  $priority
     * @return DefinesPriority
     * @throws InvalidDefinesPriorityInterfaceUsage
     */
    protected function getPriorityObject(string $priority): DefinesPriority
    {
        $priorityDefinition = $this->getPriorityDefinition();

        return new ($priorityDefinition)($priority);
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
        return array_replace_recursive(
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
        return array_replace_recursive(
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
