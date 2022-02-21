<?php

namespace Kitsune\Core\Contracts;

interface ImplementsPriority
{
    /**
     * Get the current priority.
     *
     * @return DefinesPriority
     */
    public function getPriority(): DefinesPriority;

    /**
     * Set a new priority.
     *
     * @param  string|DefinesClassPriority|DefinesEnumPriority|null  $priority
     * @return bool
     */
    public function setPriority(string|DefinesClassPriority|DefinesEnumPriority|null $priority): bool;
}
