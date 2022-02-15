<?php

namespace Kitsune\Core\Contracts;

use Kitsune\Core\Exceptions\MissingBasePathException;

interface IsSourceRepository extends CanManagePaths, ImplementsPriority
{
    /**
     * Creates a new repository for the given alias.
     */
    public function __construct(
        IsSourceNamespace $namespace,
        string $alias,
        ?string $basePath = null,
        ?array $paths = null,
        string|DefinesPriority|null $priority = null
    );

    /**
     * Get the base directory for the current source.
     *
     * @return string
     * @throws MissingBasePathException
     */
    public function getBasePath(): string;

    /**
     * Get the source paths which have been registered in the repository.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Get the namespace the source is registered for.
     *
     * @return IsSourceNamespace
     */
    public function getNamespace(): IsSourceNamespace;
}
